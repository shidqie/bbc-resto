<x-layouts.landing>
    <x-slot:title>Pesan Nasi Box — Saung Babakan Cinta</x-slot:title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        /* Custom Geocoder Search Bar */
        .leaflet-control-geocoder {
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
            border: 1px solid #e5e7eb !important;
            overflow: hidden;
        }
        .leaflet-control-geocoder-form input {
            border-radius: 12px !important;
            padding: 10px 16px !important;
            font-size: 13px !important;
            width: 260px !important;
            background: white !important;
        }
        .leaflet-control-geocoder-icon {
            background-color: #3B82F6 !important;
            border-radius: 10px !important;
        }
        .leaflet-tooltip.address-tooltip {
            background: #3B82F6; /* Primary */
            color: white;
            font-weight: 700;
            font-size: 13px;
            border: none;
            border-radius: 20px;
            padding: 6px 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            white-space: nowrap;
            text-align: center;
        }
        .leaflet-tooltip.address-tooltip::before {
            border-top-color: #3B82F6;
        }
        .leaflet-tooltip.resto-tooltip {
            background: #8B5CF6; /* Secondary */
            color: white;
            font-weight: 700;
            font-size: 12px;
            border: none;
            border-radius: 20px;
            padding: 5px 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
        }
        .leaflet-tooltip.resto-tooltip::before {
            border-top-color: #8B5CF6;
        }
        #map-container { position: relative; }
        #map-address-card {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: white;
            border-radius: 16px;
            padding: 10px 18px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.18);
            min-width: 220px;
            max-width: 90%;
            text-align: left;
            pointer-events: none;
        }
        #map-address-card .card-label {
            font-size: 11px;
            color: #6B7280;
            font-weight: 600;
            margin-bottom: 2px;
        }
        #map-address-card .card-address {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
        }
    </style>
    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-4xl mx-auto px-4">

            {{-- Header --}}
            <div class="text-center mb-10">
                <p class="text-sm text-secondary font-semibold tracking-widest uppercase mb-2">Layanan Nasi Box</p>
                <h1 class="text-4xl font-serif text-primary mb-3">Form Pemesanan Nasi Box</h1>
                <p class="text-body">Pemesanan minimal H-2 sebelum acara · Minimal 10 Box · DP 25%</p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl p-4 mb-6">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="nasiBoxForm" method="POST" action="{{ route('pesan.nasibox.store') }}">
                @csrf

                {{-- SECTION 1: Pilih Varian --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                        Pilih Varian Nasi Box
                    </h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        @php
                            // Ambil menu Nasi Box dari relasi KategoriMenu
                            $nasiBoxMenus = \App\Models\Menu::whereHas('kategori', function($q) {
                                $q->where('nama', 'like', '%Nasi Box%');
                            })->where('status', 'tersedia')->where('jenis_menu', 'nasi_box')->get();
                        @endphp
                        
                        @foreach($nasiBoxMenus as $menu)
                            <label class="paket-card cursor-pointer border-2 rounded-xl p-5 transition-all duration-200 hover:border-primary border-gray-200"
                                   data-menu-id="{{ $menu->id }}" data-harga="{{ $menu->harga }}">
                                <input type="radio" name="menu_id" value="{{ $menu->id }}" class="hidden paket-radio" {{ old('menu_id') == $menu->id ? 'checked' : '' }} required>
                                <div class="mb-3">
                                    <h3 class="text-lg font-serif text-primary font-semibold">{{ $menu->nama }}</h3>
                                    <span class="text-secondary font-bold text-lg">Rp {{ number_format($menu->harga, 0, ',', '.') }}<span class="text-sm font-normal text-body">/box</span></span>
                                </div>
                                <p class="text-body text-xs mb-3">{{ $menu->deskripsi }}</p>
                                <div class="mt-3 text-xs font-semibold text-primary opacity-0 selected-indicator transition-opacity">✓ Dipilih</div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SECTION 2: Detail Pesanan --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                        Detail Pesanan
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Tanggal Acara <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_acara" id="tanggalAcara"
                                   min="{{ \Carbon\Carbon::today()->addDays(2)->format('Y-m-d') }}"
                                   value="{{ old('tanggal_acara') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition" required>
                            <p id="tanggal-warning" class="text-red-500 text-xs mt-1 hidden">Pesanan nasi box maksimal H-2 sebelum acara.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Jumlah Box <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_box" id="jumlahBox" min="10" value="{{ old('jumlah_box', 10) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition" required>
                             <p id="jumlah-warning" class="text-red-500 text-xs mt-1 hidden">Minimal order 10 box.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Nama Pemesan <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_pemesan" value="{{ old('nama_pemesan') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Nomor Kontak (WhatsApp) <span class="text-red-500">*</span></label>
                            <div class="flex">
                                <span class="inline-flex items-center px-4 font-semibold text-gray-600 bg-gray-50 border border-r-0 border-gray-200 rounded-l-xl">
                                    +62
                                </span>
                                <input type="number" inputmode="numeric" name="kontak" value="{{ old('kontak') }}" placeholder="81234567890"
                                       class="w-full border border-gray-200 rounded-r-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition [&::-webkit-inner-spin-button]:appearance-none" required>
                            </div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-body mb-2">Metode Pengiriman <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="metode_pengiriman" value="pickup" class="metode-radio" checked>
                                    <span class="text-sm">Pickup Sendiri</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="metode_pengiriman" value="delivery" class="metode-radio">
                                    <span class="text-sm">Delivery (Kirim ke alamat)</span>
                                </label>
                            </div>
                        </div>

                        <div id="deliverySection" class="md:col-span-2 hidden mb-4">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-body mb-1">Alamat Lengkap Pengiriman <span class="text-red-500">*</span></label>
                                <textarea name="alamat" id="alamatDelivery" rows="2"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition">{{ old('alamat') }}</textarea>
                                <p class="text-xs text-body/60 mt-1">💡 Tip: Cari alamat lewat ikon 🔍 di peta, lalu geser pin ke titik yang tepat.</p>
                            </div>

                            {{-- Map Container --}}
                            <div id="map-container" class="rounded-2xl overflow-hidden border border-gray-200 shadow-md mb-3" style="height: 340px; position:relative;">
                                {{-- Address Card Overlay --}}
                                <div id="map-address-card">
                                    <div class="card-label">📍 Alamat Kamu</div>
                                    <div class="card-address" id="cardAlamat">Geser pin ke lokasi kamu...</div>
                                </div>
                                <button type="button" onclick="locateUser()" class="absolute bottom-6 right-4 z-[1000] bg-white w-10 h-10 flex items-center justify-center rounded-xl shadow-lg border border-gray-200 text-primary hover:bg-gray-50 transition" title="Temukan Lokasi Saya">
                                    <i class="ph-bold ph-crosshair text-xl"></i>
                                </button>
                                <div id="map" style="width:100%; height:100%;"></div>
                            </div>
                            
                            <input type="hidden" name="latitude" id="inputLat">
                            <input type="hidden" name="longitude" id="inputLng">
                            <input type="hidden" name="jarak_km" id="inputJarak">
                            
                            {{-- Jarak Info Card --}}
                            <div class="grid grid-cols-2 gap-3 mb-3">
                                <div class="bg-secondary/5 p-3 rounded-xl border border-secondary/10 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-secondary flex items-center justify-center text-white flex-shrink-0">
                                        <i class="ph-fill ph-storefront text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-secondary font-semibold">Titik Resto</p>
                                        <p class="text-xs font-bold text-gray-700">Saung Babakan Cinta</p>
                                    </div>
                                </div>
                                <div class="bg-primary/5 p-3 rounded-xl border border-primary/10 flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-primary flex items-center justify-center text-white flex-shrink-0">
                                        <i class="ph-fill ph-truck text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-xs text-primary font-semibold">Jarak Pengiriman</p>
                                        <p class="text-sm font-bold text-gray-700" id="textJarak">– km</p>
                                    </div>
                                </div>
                            </div>

                            <p id="jarakWarning" class="text-red-500 text-xs mb-3 hidden"></p>
                        </div>

                        <div id="pickupSection" class="md:col-span-2">
                            <label class="block text-sm font-semibold text-body mb-1">Alamat (Optional untuk pickup)</label>
                            <textarea name="alamat" id="alamatPickup" rows="2"
                                      class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition">-</textarea>
                        </div>

                        <div class="md:col-span-2 mt-2">
                            <label class="block text-sm font-semibold text-body mb-1">Catatan Tambahan</label>
                            <textarea name="catatan" rows="2"
                                      class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Ringkasan & Submit --}}
                <div class="bg-primary text-white rounded-2xl p-6 shadow-md">
                    <h2 class="text-lg font-serif mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-sm font-bold">3</span>
                        Ringkasan Pesanan
                    </h2>
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-white/70">Harga per Box</span>
                            <span id="harga-per-box" class="font-semibold">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-white/70">Jumlah Box</span>
                            <span id="summary-jumlah" class="font-semibold">0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-white/70">Ongkos Kirim</span>
                            <span id="summary-ongkir" class="font-semibold text-yellow-300">Rp 0</span>
                        </div>
                        <div class="border-t border-white/20 pt-2 flex justify-between text-base">
                            <span class="font-semibold">Total Tagihan</span>
                            <span id="total-tagihan" class="font-bold text-secondary">Rp 0</span>
                        </div>
                        <div class="flex justify-between text-base">
                            <span>DP yang Harus Dibayar <span class="text-white/70 text-xs">(25%)</span></span>
                            <span id="dp-amount" class="font-bold text-yellow-300">Rp 0</span>
                        </div>
                    </div>
                    <button type="submit" id="submitBtn"
                            class="w-full bg-secondary hover:bg-secondary/90 text-white font-bold py-3.5 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        Lanjut ke Pembayaran →
                    </button>
                </div>

            </form>
        </div>
    </section>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        const minDate = "{{ \Carbon\Carbon::today()->addDays(2)->format('Y-m-d') }}";
        let hargaMenu = 0;
        let selectedMenuId = null;
        let metodePengiriman = 'pickup';
        let jarakKm = 0;

        // Peta & Jarak
        const bbcLat = -6.8115651;
        const bbcLng = 107.5459389;
        let map, marker, restoMarker;

        function initMap() {
            if(map) return;
            map = L.map('map').setView([bbcLat, bbcLng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            const restoIcon = L.divIcon({
                html: '<i class="ph-fill ph-storefront text-[#8B5CF6] text-4xl drop-shadow-md"></i>',
                className: '',
                iconSize: [36, 36],
                iconAnchor: [18, 36],
                tooltipAnchor: [0, -28]
            });
            const userIcon = L.divIcon({
                html: '<i class="ph-fill ph-map-pin text-[#3B82F6] text-4xl drop-shadow-md"></i>',
                className: '',
                iconSize: [36, 36],
                iconAnchor: [18, 36],
                tooltipAnchor: [0, -28]
            });

            restoMarker = L.marker([bbcLat, bbcLng], {icon: restoIcon}).addTo(map);
            restoMarker.bindTooltip("Saung Babakan Cinta", {permanent: true, direction: 'top', offset: [0, -10], className: 'resto-tooltip'}).openTooltip();

            marker = L.marker([bbcLat, bbcLng], {icon: userIcon, draggable: true}).addTo(map);
            marker.bindTooltip("Alamatmu di sini", {permanent: true, direction: 'top', offset: [0, -10], className: 'address-tooltip'}).openTooltip();

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                document.getElementById('inputLat').value = pos.lat;
                document.getElementById('inputLng').value = pos.lng;
                hitungJarakOSRM(bbcLat, bbcLng, pos.lat, pos.lng);
                updateAlamatText(pos.lat, pos.lng);
            });

            // Coba dapatkan lokasi pengguna saat ini (GPS) secara otomatis
            locateUser(false);

            // Add Search Bar
            L.Control.geocoder({
                defaultMarkGeocode: false,
                placeholder: "Cari lokasi atau alamat..."
            })
            .on('markgeocode', function(e) {
                const bbox = e.geocode.bbox;
                const poly = L.polygon([
                    bbox.getSouthEast(),
                    bbox.getNorthEast(),
                    bbox.getNorthWest(),
                    bbox.getSouthWest()
                ]);
                map.fitBounds(poly.getBounds());
                
                marker.setLatLng(e.geocode.center);
                
                const pos = marker.getLatLng();
                document.getElementById('inputLat').value = pos.lat;
                document.getElementById('inputLng').value = pos.lng;
                hitungJarakOSRM(bbcLat, bbcLng, pos.lat, pos.lng);
                
                // Update card text
                if (e.geocode.name) {
                    document.getElementById('cardAlamat').textContent = e.geocode.name;
                    document.getElementById('alamatDelivery').value = e.geocode.name;
                }
            })
            .addTo(map);
        }

        function locateUser(showAlert = true) {
            if (showAlert) document.getElementById('cardAlamat').textContent = "Mencari lokasi GPS...";
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;
                    
                    marker.setLatLng([userLat, userLng]);
                    map.setView([userLat, userLng], 15);
                    
                    document.getElementById('inputLat').value = userLat;
                    document.getElementById('inputLng').value = userLng;
                    
                    hitungJarakOSRM(bbcLat, bbcLng, userLat, userLng);
                    updateAlamatText(userLat, userLng);
                }, function(error) {
                    console.log("Geolocation error:", error);
                    if (showAlert) {
                        alert("Gagal mendeteksi lokasi. Pastikan izin lokasi (GPS) diaktifkan di browser.");
                        document.getElementById('cardAlamat').textContent = "Geser pin ke lokasi kamu...";
                    }
                }, { enableHighAccuracy: true });
            } else if (showAlert) {
                alert("Browser Anda tidak mendukung fitur lokasi GPS.");
            }
        }

        async function updateAlamatText(lat, lng) {
            document.getElementById('cardAlamat').textContent = "Mencari alamat...";
            try {
                const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}`);
                const data = await res.json();
                if(data && data.display_name) {
                    const shortAddr = data.display_name.split(',').slice(0, 3).join(',');
                    document.getElementById('cardAlamat').textContent = shortAddr;
                    if(!document.getElementById('alamatDelivery').value) {
                         document.getElementById('alamatDelivery').value = data.display_name;
                    }
                } else {
                    document.getElementById('cardAlamat').textContent = "Lokasi ditandai";
                }
            } catch(e) {
                document.getElementById('cardAlamat').textContent = "Lokasi ditandai";
            }
        }

        async function hitungJarakOSRM(lat1, lng1, lat2, lng2) {
            try {
                const res = await fetch(`https://router.project-osrm.org/route/v1/driving/${lng1},${lat1};${lng2},${lat2}?overview=false`);
                const data = await res.json();
                if(data.routes && data.routes.length > 0) {
                    jarakKm = data.routes[0].distance / 1000;
                    document.getElementById('inputJarak').value = jarakKm.toFixed(2);
                    document.getElementById('textJarak').textContent = jarakKm.toFixed(2) + ' km';
                    
                    if(jarakKm > 30) {
                        document.getElementById('jarakWarning').textContent = 'Di luar area layanan (maks 30 km).';
                        document.getElementById('jarakWarning').classList.remove('hidden');
                        document.getElementById('submitBtn').disabled = true;
                    } else {
                        document.getElementById('jarakWarning').classList.add('hidden');
                        document.getElementById('submitBtn').disabled = false;
                        hitungTotalPreview(); // Update ongkir via ajax
                    }
                }
            } catch(e) {
                console.error("OSRM Error", e);
            }
        }

        document.querySelectorAll('.metode-radio').forEach(r => {
            r.addEventListener('change', (e) => {
                metodePengiriman = e.target.value;
                if(metodePengiriman === 'delivery') {
                    document.getElementById('deliverySection').classList.remove('hidden');
                    document.getElementById('pickupSection').classList.add('hidden');
                    document.getElementById('alamatDelivery').required = true;
                    document.getElementById('alamatPickup').required = false;
                    document.getElementById('alamatPickup').name = '';
                    document.getElementById('alamatDelivery').name = 'alamat';
                    setTimeout(initMap, 200);
                } else {
                    document.getElementById('deliverySection').classList.add('hidden');
                    document.getElementById('pickupSection').classList.remove('hidden');
                    document.getElementById('alamatDelivery').required = false;
                    document.getElementById('alamatPickup').required = true;
                    document.getElementById('alamatDelivery').name = '';
                    document.getElementById('alamatPickup').name = 'alamat';
                }
                hitungTotalPreview();
            });
        });

        // Pilih Varian
        document.querySelectorAll('.paket-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.paket-card').forEach(c => {
                    c.classList.remove('border-primary', 'bg-primary/5');
                    c.classList.add('border-gray-200');
                    c.querySelector('.selected-indicator').style.opacity = '0';
                });
                card.classList.add('border-primary', 'bg-primary/5');
                card.classList.remove('border-gray-200');
                card.querySelector('.selected-indicator').style.opacity = '1';
                card.querySelector('.paket-radio').checked = true;
                
                selectedMenuId = card.dataset.menuId;
                hargaMenu = parseInt(card.dataset.harga);
                hitungTotalPreview();
            });
        });

        function formatRp(n) {
            return 'Rp ' + n.toLocaleString('id-ID');
        }

        async function hitungTotalPreview() {
            const jumlah = parseInt(document.getElementById('jumlahBox').value) || 0;
            
            // Tampilan statis
            document.getElementById('harga-per-box').textContent = formatRp(hargaMenu);
            document.getElementById('summary-jumlah').textContent = jumlah;
            
            if(!selectedMenuId) return;

            try {
                const res = await fetch("{{ route('pesan.nasibox.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        menu_id: selectedMenuId,
                        jumlah_box: jumlah,
                        metode_pengiriman: metodePengiriman,
                        jarak_km: jarakKm
                    })
                });
                const data = await res.json();
                
                if(res.ok) {
                    document.getElementById('summary-ongkir').textContent = formatRp(data.ongkir || 0);
                    document.getElementById('total-tagihan').textContent = formatRp(data.total);
                    document.getElementById('dp-amount').textContent = formatRp(data.dp);
                    
                    if(data.ongkir === 0 && metodePengiriman === 'delivery' && jumlah >= 25) {
                         document.getElementById('summary-ongkir').textContent = 'GRATIS (Tier Tercapai)';
                    }
                    
                    document.getElementById('submitBtn').disabled = false;
                } else {
                    alert(data.error || "Gagal menghitung tagihan.");
                    document.getElementById('submitBtn').disabled = true;
                }
            } catch(e) {
                console.error(e);
            }
        }

        document.getElementById('jumlahBox').addEventListener('input', function() {
            const warn = document.getElementById('jumlah-warning');
            const jumlah = parseInt(this.value);
            if (jumlah < 10) {
                warn.classList.remove('hidden');
                warn.textContent = "Minimal order 10 box.";
            } else if (metodePengiriman === 'delivery' && jumlah < 25) {
                warn.classList.remove('hidden');
                warn.textContent = "Minimal order 25 box untuk Delivery. Ubah ke Pickup.";
            } else {
                warn.classList.add('hidden');
            }
            hitungTotalPreview();
        });

        // Validasi tanggal
        document.getElementById('tanggalAcara').addEventListener('change', function() {
            const warn = document.getElementById('tanggal-warning');
            if (this.value < minDate) {
                warn.classList.remove('hidden');
            } else {
                warn.classList.add('hidden');
            }
        });
    </script>
    @endpush
</x-layouts.landing>
