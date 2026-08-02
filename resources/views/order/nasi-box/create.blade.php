<x-layouts.landing>
    <x-slot:title>Pesan Nasi Box — Saung Babakan Cinta</x-slot:title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        /* Custom Geocoder Search Bar (Google Maps Style) */
        .leaflet-control-geocoder {
            position: absolute !important;
            top: 16px !important;
            left: 50% !important;
            transform: translateX(-50%) !important;
            z-index: 1000 !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2) !important;
            border: none !important;
            overflow: hidden;
            background: white !important;
            margin: 0 !important;
            display: flex;
            align-items: center;
            pointer-events: auto; /* Required since parent pointer-events is none */
        }
        .leaflet-control-geocoder-form {
            display: flex;
            align-items: center;
        }
        .leaflet-control-geocoder-form input {
            border: none !important;
            padding: 12px 16px 12px 0 !important;
            font-size: 14px !important;
            width: 280px !important;
            background: transparent !important;
            color: #374151 !important;
        }
        .leaflet-control-geocoder-form input:focus {
            outline: none !important;
            box-shadow: none !important;
        }
        .leaflet-control-geocoder-icon {
            background-color: transparent !important;
            border-radius: 0 !important;
            width: 40px !important;
            height: 40px !important;
            background-size: 20px 20px !important;
            opacity: 0.6;
        }
        .leaflet-tooltip.address-tooltip, .leaflet-tooltip.resto-tooltip {
            background: white;
            color: #111827;
            font-weight: 600;
            font-size: 12px;
            border: 1px solid #F3F4F6;
            border-radius: 8px;
            padding: 6px 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            text-align: center;
        }
        .leaflet-tooltip.address-tooltip::before, .leaflet-tooltip.resto-tooltip::before {
            border-top-color: white;
        }
        #map-container { position: relative; }
        #map-address-card {
            position: absolute;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: white;
            border-radius: 12px;
            padding: 14px 20px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            min-width: 280px;
            max-width: 90%;
            text-align: center;
            pointer-events: none;
            border: 1px solid #E5E7EB;
        }
        #map-address-card .card-label {
            font-size: 11px;
            color: #6B7280;
            font-weight: 600;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        #map-address-card .card-address {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            line-height: 1.4;
        }
    </style>
    <section class="py-16 bg-white min-h-screen">
        <div class="max-w-7xl mx-auto px-4">

            {{-- Header --}}
            <div class="mb-10">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Pemesanan Nasi Box</h1>
                <p class="text-gray-500 text-sm">Minimal 10 Box · Pesan maksimal H-2 sebelum acara (DP 25%)</p>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 rounded-3xl p-4 mb-6">
                    <ul class="list-disc list-inside text-sm space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="nasiBoxForm" method="POST" action="{{ route('pesan.nasibox.store') }}">
                @csrf
                <div class="grid lg:grid-cols-3 gap-12 items-start">
                    
                    {{-- LEFT COLUMN: Form Input --}}
                    <div class="lg:col-span-2 divide-y divide-gray-100">
                        
                        {{-- SECTION 1: Pilih Varian --}}
                        <div class="pb-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">1. Pilih Varian Nasi Box</h2>
                    <div class="grid md:grid-cols-3 gap-4">
                        @foreach($pakets as $paket)
                            <label class="paket-card cursor-pointer border-2 rounded-3xl p-5 transition-all duration-200 hover:border-primary border-gray-200"
                                   data-paket-id="{{ $paket->id }}" data-harga="{{ $paket->harga_jual }}">
                                <input type="radio" name="paket_id" value="{{ $paket->id }}" class="sr-only paket-radio" {{ old('paket_id') == $paket->id ? 'checked' : '' }} required>
                                <div class="mb-3">
                                    <h3 class="text-lg font-serif text-primary font-semibold">{{ $paket->nama_menu }}</h3>
                                    <span class="text-secondary font-bold text-lg">Rp {{ number_format($paket->harga_jual, 0, ',', '.') }}<span class="text-sm font-normal text-body">/box</span></span>
                                </div>
                                <p class="text-body text-xs mb-3">{{ $paket->deskripsi }}</p>
                                <div class="mt-3 text-xs font-semibold text-primary opacity-0 selected-indicator transition-opacity">✓ Dipilih</div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SECTION 1.5: Pilih Komponen Lauk --}}
                <div id="sec-komponen" class="py-8 hidden">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">Pilih Menu Lauk</h2>
                    <div id="komponen-container" class="space-y-4">
                        <!-- Komponen radio buttons will be loaded here via JS -->
                    </div>
                </div>

                {{-- SECTION 2: Detail Pesanan --}}
                <div class="py-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">2. Data Pemesan & Pengiriman</h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal Acara <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_acara" id="tanggalAcara"
                                   min="{{ \Carbon\Carbon::today()->addDays(2)->format('Y-m-d') }}"
                                   value="{{ old('tanggal_acara') }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-gray-50/50" required>
                            <p id="tanggal-warning" class="text-red-500 text-xs mt-1 hidden">Pesanan nasi box maksimal H-2 sebelum acara.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jumlah Box <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_box" id="jumlahBox" min="10" value="{{ old('jumlah_box', 10) }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-gray-50/50" required>
                             <p id="jumlah-warning" class="text-red-500 text-xs mt-1 hidden">Minimal order 10 box.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Pemesan <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_pemesan" value="{{ old('nama_pemesan', optional(auth('pelanggan')->user())->nama ?? '') }}"
                                   class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-gray-50/50" required>
                        </div>
                        <div>
                            <x-input-wa name="kontak" label="Nomor WhatsApp" :value="optional(auth('pelanggan')->user())->nomor_telepon ?? ''" :required="true" hint="Nomor aktif WhatsApp untuk konfirmasi pesanan." />
                        </div>

                        <div class="md:col-span-2 mt-4 border-t border-gray-100 pt-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Metode Pengiriman <span class="text-red-500">*</span></label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="metode_pengiriman" value="pickup" class="metode-radio w-4 h-4 accent-primary" checked>
                                    <span class="text-sm font-medium text-gray-900">Pickup Sendiri</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" name="metode_pengiriman" value="delivery" class="metode-radio w-4 h-4 accent-primary">
                                    <span class="text-sm font-medium text-gray-900">Delivery (Kirim ke alamat)</span>
                                </label>
                            </div>
                        </div>

                        <div id="deliverySection" class="md:col-span-2 hidden mb-4">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Venue / Gedung (Opsional)</label>
                                <input type="text" name="alamat_venue" value="{{ old('alamat_venue') }}" placeholder="Contoh: Gedung Sabuga / Aula Serbaguna"
                                       class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-gray-50/50 mb-4">
                                       
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat Lengkap <span class="text-red-500">*</span></label>
                                <textarea name="alamat" id="alamatDelivery" rows="2"
                                        class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-gray-50/50">{{ old('alamat', auth()->user()->alamat ?? '') }}</textarea>
                            </div>

                            {{-- Map Container --}}
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Lokasi Pengiriman <span class="text-red-500">*</span></label>
                            <p class="text-xs text-body/60 mb-2">💡 Tip: Cari alamat lewat ikon 🔍 di peta, lalu geser pin ke titik yang tepat.</p>
                            <div id="map-container" class="rounded-[2.25rem] overflow-hidden border border-gray-200 shadow-md mb-3 z-0" style="height: 340px; position:relative;">
                                {{-- Address Card Overlay --}}
                                <div id="map-address-card">
                                    <div class="card-label">📍 Alamat Kamu</div>
                                    <div class="card-address" id="cardAlamat">Geser pin ke lokasi kamu...</div>
                                </div>
                                <button type="button" onclick="locateUser()" class="absolute bottom-6 right-4 z-[1000] bg-white w-10 h-10 flex items-center justify-center rounded-full shadow-lg border border-gray-200 text-primary hover:bg-gray-50 transition" title="Temukan Lokasi Saya">
                                    <i class="ph-bold ph-crosshair text-xl"></i>
                                </button>
                                <div id="map" style="width:100%; height:100%;"></div>
                            </div>
                            
                            <input type="hidden" name="latitude" id="inputLat" value="{{ old('latitude', auth()->user()->latitude ?? '') }}">
                            <input type="hidden" name="longitude" id="inputLng" value="{{ old('longitude', auth()->user()->longitude ?? '') }}">
                            <input type="hidden" name="jarak_km" id="inputJarak">
                            
                            {{-- Jarak Info Card Minimalist --}}
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5 mt-4">Jarak Pengiriman (Otomatis)</label>
                            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                                <div class="flex-1 bg-white p-3 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 flex-shrink-0">
                                        <i class="ph ph-storefront text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Titik Resto</p>
                                        <p class="text-xs font-bold text-gray-800">Saung Babakan Cinta</p>
                                    </div>
                                </div>
                                <div class="flex-1 bg-white p-3 rounded-3xl border border-gray-100 shadow-sm flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 flex-shrink-0">
                                        <i class="ph ph-truck text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Jarak Pengiriman</p>
                                        <p class="text-xs font-bold text-gray-800" id="textJarak">– km</p>
                                    </div>
                                </div>
                            </div>

                            <p id="jarakWarning" class="text-red-500 text-xs mb-3 hidden"></p>
                        </div>

                        <div id="pickupSection" class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat (Optional untuk pickup)</label>
                            <textarea name="alamat" id="alamatPickup" rows="2"
                                      class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-gray-50/50">-</textarea>
                        </div>

                        <div class="md:col-span-2 mt-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan Tambahan</label>
                            <textarea name="catatan" rows="2"
                                      class="w-full border border-gray-200 rounded-2xl px-4 py-2.5 text-sm focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary transition bg-gray-50/50">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- SECTION 3: Opsi Pembayaran --}}
                <div class="py-8">
                    <h2 class="text-lg font-bold text-gray-900 mb-5">3. Pembayaran</h2>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <label class="flex-1 flex items-center gap-3 border border-primary bg-primary/5 rounded-2xl px-4 py-3.5 cursor-pointer transition">
                            <input type="radio" name="opsi_pembayaran" value="dp" checked class="w-4 h-4 accent-primary" onchange="updatePaymentLabel(this.value)">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Bayar DP (25%)</p>
                                <p class="text-xs text-gray-500">Sisa dibayar nanti</p>
                            </div>
                        </label>
                        <label class="flex-1 flex items-center gap-3 border border-gray-200 bg-white rounded-2xl px-4 py-3.5 cursor-pointer hover:border-primary/30 transition">
                            <input type="radio" name="opsi_pembayaran" value="lunas" class="w-4 h-4 accent-primary" onchange="updatePaymentLabel(this.value)">
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Bayar Lunas (100%)</p>
                                <p class="text-xs text-gray-500">Bayar penuh di awal</p>
                            </div>
                        </label>
                    </div>
                </div>
                    </div> {{-- END LEFT COLUMN --}}

                    {{-- RIGHT COLUMN: Ringkasan & Submit (Sticky) --}}
                    <div class="lg:col-span-1 sticky top-28">
                        {{-- SECTION 4: Ringkasan & Submit --}}
                        <div class="bg-gray-50/50 border border-gray-200 rounded-3xl p-6">
                            <h2 class="text-base font-bold text-gray-900 mb-4 pb-4 border-b border-gray-200">
                                Ringkasan Pesanan
                            </h2>
                            <div class="space-y-3 text-sm mb-6">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Harga per Box</span>
                                    <span id="harga-per-box" class="font-semibold text-gray-900">Rp 0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Jumlah Box</span>
                                    <span id="summary-jumlah" class="font-semibold text-gray-900">0</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Ongkos Kirim</span>
                                    <span id="summary-ongkir" class="font-semibold text-gray-900">Rp 0</span>
                                </div>
                                <div class="border-t border-gray-200 pt-3 flex justify-between text-base mt-2">
                                    <span class="font-semibold text-gray-900">Total Tagihan</span>
                                    <span id="total-tagihan" class="font-bold text-gray-900">Rp 0</span>
                                </div>
                                <div class="flex justify-between text-base bg-amber-50 rounded-2xl p-3 mt-4 border border-amber-100">
                                    <span id="label-payment" class="text-amber-800 font-medium">DP (25%)</span>
                                    <span id="dp-amount" class="font-bold text-amber-600">Rp 0</span>
                                </div>
                            </div>
                            <button type="submit" id="submitBtn"
                                    class="w-full bg-primary hover:bg-primary-container text-white font-semibold py-3 rounded-2xl transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                Lanjut Pembayaran
                            </button>
                        </div>
                    </div> {{-- END RIGHT COLUMN --}}
                </div> {{-- END GRID --}}

            </form>
        </div>
    </section>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    <script>
        const minDate = "{{ \Carbon\Carbon::today()->addDays(2)->format('Y-m-d') }}";
        let hargaMenu = 0;
        let selectedPaketId = null;
        let metodePengiriman = 'pickup';
        let jarakKm = 0;

        // Peta & Jarak
        const bbcLat = -6.8115651;
        const bbcLng = 107.5459389;
        let map, marker, restoMarker;

        function initMap() {
            if(map) return;
            // Initialize Map
            map = L.map('map', {
                zoomControl: false // Disable default zoom control
            }).setView([bbcLat, bbcLng], 14);

            // Add Zoom Control to Bottom Left
            L.control.zoom({
                position: 'bottomleft'
            }).addTo(map);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);

            const restoIcon = L.divIcon({
                html: '<div class="w-8 h-8 bg-white border border-gray-200 shadow-sm rounded-full flex items-center justify-center text-gray-600"><i class="ph ph-storefront text-lg"></i></div>',
                className: '',
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                tooltipAnchor: [0, -20]
            });
            const userIcon = L.divIcon({
                html: '<div class="w-8 h-8 bg-primary shadow-md shadow-primary/30 rounded-full flex items-center justify-center text-white"><i class="ph-bold ph-map-pin text-lg"></i></div>',
                className: '',
                iconSize: [32, 32],
                iconAnchor: [16, 16],
                tooltipAnchor: [0, -20]
            });

            restoMarker = L.marker([bbcLat, bbcLng], {icon: restoIcon}).addTo(map);
            restoMarker.bindTooltip("Saung Babakan Cinta", {permanent: true, direction: 'top', offset: [0, -10], className: 'resto-tooltip'}).openTooltip();

            let initLat = document.getElementById('inputLat').value || bbcLat;
            let initLng = document.getElementById('inputLng').value || bbcLng;
            marker = L.marker([initLat, initLng], {icon: userIcon, draggable: true}).addTo(map);
            marker.bindTooltip("Alamatmu di sini", {permanent: true, direction: 'top', offset: [0, -10], className: 'address-tooltip'}).openTooltip();

            marker.on('dragend', function(e) {
                const pos = marker.getLatLng();
                document.getElementById('inputLat').value = pos.lat;
                document.getElementById('inputLng').value = pos.lng;
                hitungJarakOSRM(bbcLat, bbcLng, pos.lat, pos.lng);
                updateAlamatText(pos.lat, pos.lng);
            });

            if (document.getElementById('inputLat').value) {
                map.setView([initLat, initLng], 14);
                hitungJarakOSRM(bbcLat, bbcLng, initLat, initLng);
            } else {
                // Coba dapatkan lokasi pengguna saat ini (GPS) secara otomatis
                locateUser(false);
            }

            // Add Search Control (Geocoder)
            const geocoder = L.Control.geocoder({
                defaultMarkGeocode: false,
                placeholder: 'Cari lokasi atau alamat...'
            }).addTo(map);

            // Detach and move to map container for perfect absolute centering
            const mapContainer = document.getElementById('map');
            const geocoderContainer = geocoder.getContainer();
            mapContainer.appendChild(geocoderContainer);

            geocoder.on('markgeocode', function(e) {
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
                
                selectedPaketId = card.dataset.paketId;
                hargaMenu = parseInt(card.dataset.harga);
                loadKomponen(selectedPaketId);
                hitungTotalPreview();
            });
        });

        async function loadKomponen(paketId) {
            const sec = document.getElementById('sec-komponen');
            const container = document.getElementById('komponen-container');
            sec.classList.remove('hidden');
            container.innerHTML = '<p class="text-body text-sm">Memuat komponen...</p>';

            const res = await fetch(`/pesan/catering/komponen/${paketId}`);
            const komponens = await res.json();

            container.innerHTML = '';
            komponens.forEach(komp => {
                const div = document.createElement('div');
                div.className = 'border border-gray-100 rounded-3xl p-4 bg-canvas';
                
                if (komp.tipe === 'fixed') {
                    div.innerHTML = `
                        <p class="text-sm font-semibold text-body mb-2">${komp.nama_komponen}</p>
                        <div class="flex flex-wrap gap-2">
                            ${komp.opsi.map(o => `
                                <div class="flex items-center gap-2 px-3 py-1 bg-primary/10 text-primary text-xs rounded-full font-medium">
                                    ${o.menu.foto ? `<img src="/storage/${o.menu.foto}" alt="${o.menu.nama}" class="w-5 h-5 rounded-full object-cover">` : ''}
                                    <span>${o.menu.nama} ✓</span>
                                </div>
                            `).join('')}
                        </div>`;
                } else {
                    div.innerHTML = `
                        <p class="text-sm font-semibold text-body mb-2">${komp.nama_komponen} <span class="text-secondary text-xs">(pilih 1)</span></p>
                        <div class="flex flex-wrap gap-2">
                            ${komp.opsi.map(o => `
                                <label class="cursor-pointer group relative">
                                    <input type="radio" name="komponen[${komp.id}]" value="${o.menu.id}" class="opacity-0 absolute w-0 h-0 peer" required>
                                    <div class="flex items-center gap-2 px-3 py-1.5 border border-gray-200 bg-white rounded-full font-medium text-body text-xs peer-checked:bg-primary peer-checked:border-primary peer-checked:text-white transition-all group-hover:border-primary/50">
                                        ${o.menu.foto ? `<img src="/storage/${o.menu.foto}" alt="${o.menu.nama}" class="w-5 h-5 rounded-full object-cover">` : ''}
                                        <span>${o.menu.nama}</span>
                                    </div>
                                </label>`).join('')}
                        </div>`;
                }
                container.appendChild(div);
            });
        }

        function formatRp(n) {
            return 'Rp ' + n.toLocaleString('id-ID');
        }

        let currentTotal = 0;
        let currentDp = 0;

        function updatePaymentLabel(val) {
            const label = document.getElementById('label-payment');
            const amount = document.getElementById('dp-amount');
            if (val === 'lunas') {
                label.innerHTML = 'Total Pembayaran <span class="text-amber-700/70 text-xs">(100%)</span>';
                amount.textContent = formatRp(currentTotal);
            } else {
                label.innerHTML = 'DP Pembayaran <span class="text-amber-700/70 text-xs">(25%)</span>';
                amount.textContent = formatRp(currentDp);
            }
            
            // update styling borders
            document.querySelectorAll('input[name="opsi_pembayaran"]').forEach(el => {
                const parent = el.closest('label');
                if(el.checked) {
                    parent.classList.add('border-primary', 'bg-primary/5');
                    parent.classList.remove('border-gray-200', 'bg-white');
                } else {
                    parent.classList.remove('border-primary', 'bg-primary/5');
                    parent.classList.add('border-gray-200', 'bg-white');
                }
            });
        }

        async function hitungTotalPreview() {
            const jumlah = parseInt(document.getElementById('jumlahBox').value) || 0;
            
            // Tampilan statis
            document.getElementById('harga-per-box').textContent = formatRp(hargaMenu);
            document.getElementById('summary-jumlah').textContent = jumlah;
            
            if(!selectedPaketId) return;

            try {
                const res = await fetch("{{ route('pesan.nasibox.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        paket_id: selectedPaketId,
                        jumlah_box: jumlah,
                        metode_pengiriman: metodePengiriman,
                        jarak_km: jarakKm
                    })
                });
                const data = await res.json();
                
                if(res.ok) {
                    document.getElementById('summary-ongkir').textContent = formatRp(data.ongkir || 0);
                    document.getElementById('total-tagihan').textContent = formatRp(data.total);
                    currentTotal = data.total;
                    currentDp = data.dp;
                    updatePaymentLabel(document.querySelector('input[name="opsi_pembayaran"]:checked').value);
                    
                    // Tier gratis ongkir akan otomatis menjadi Rp 0 dari server
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
