<x-layouts.landing>
    <x-slot:title>Pesan Catering — Saung Babakan Cinta</x-slot:title>

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
    <section class="py-16 bg-canvas min-h-screen">
        <div class="max-w-4xl mx-auto px-4">

            @guest
            <div class="bg-primary/5 border border-primary/20 text-primary rounded-xl p-4 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                <div>
                    <h3 class="font-bold text-sm mb-1 text-gray-900">Pesan lebih cepat, pantau lebih mudah!</h3>
                    <p class="text-xs text-gray-600">Anda dapat checkout sebagai tamu, namun mendaftar akun akan menyimpan riwayat pesanan Anda.</p>
                </div>
                <div class="flex gap-2 shrink-0">
                    <a href="{{ route('login') }}" class="text-xs font-semibold bg-white border-2 border-primary text-primary px-4 py-1.5 rounded-md hover:bg-primary/5 transition-all shadow-sm">Login</a>
                    <a href="{{ route('register') }}" class="text-xs font-semibold bg-primary text-white border-2 border-primary px-4 py-1.5 rounded-md hover:opacity-90 transition-all shadow-sm">Daftar Sekarang</a>
                </div>
            </div>
            @endguest

            {{-- Header --}}
            <div class="text-center mb-10">
                <p class="text-sm text-secondary font-semibold tracking-widest uppercase mb-2">Layanan Catering</p>
                <h1 class="text-4xl font-serif text-primary mb-3">Form Pemesanan Catering</h1>
                <p class="text-body">Pemesanan minimal H-14 sebelum acara · DP 50%</p>
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

            <form id="cateringForm" method="POST" action="{{ route('pesan.catering.store') }}">
                @csrf

                {{-- SECTION 1: Detail Acara --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">1</span>
                        Detail Acara
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Tanggal Acara <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_acara" id="tanggalAcara"
                                   min="{{ \Carbon\Carbon::today()->addDays(14)->format('Y-m-d') }}"
                                   value="{{ old('tanggal_acara') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition" required>
                            <p id="tanggal-warning" class="text-red-500 text-xs mt-1 hidden">Pemesanan catering minimal H-14 sebelum acara.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Jumlah Porsi <span class="text-red-500">*</span></label>
                            <input type="number" name="jumlah_porsi" id="jumlahPorsi" min="1" value="{{ old('jumlah_porsi', 50) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition" required>
                        </div>
                    </div>
                </div>

{{-- SECTION 2: Pilih Paket --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">2</span>
                        Pilih Paket
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        @foreach($pakets->where('jenis_paket','catering') as $paket)
                            <label class="paket-card cursor-pointer border-2 rounded-xl p-5 transition-all duration-200 hover:border-primary border-gray-200"
                                   data-paket-id="{{ $paket->id }}" data-harga="{{ $paket->harga }}">
                                <input type="radio" name="paket_id" value="{{ $paket->id }}" class="hidden paket-radio" {{ old('paket_id') == $paket->id ? 'checked' : '' }}>
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="text-lg font-serif text-primary font-semibold">{{ $paket->nama_paket }}</h3>
                                    <span class="text-secondary font-bold text-lg">Rp {{ number_format($paket->harga, 0, ',', '.') }}<span class="text-sm font-normal text-body">/porsi</span></span>
                                </div>
                                <p class="text-body text-sm mb-3">{{ $paket->deskripsi }}</p>
                                <ul class="text-xs text-body space-y-1">
                                    @foreach($paket->komponens->sortBy('urutan') as $komp)
                                        <li class="flex items-center gap-1.5">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $komp->tipe === 'fixed' ? 'bg-primary' : 'bg-secondary' }} flex-shrink-0"></span>
                                            <span>{{ $komp->nama_komponen }}
                                                @if($komp->tipe === 'choice')<span class="text-secondary">(pilih 1)</span>@endif
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                                <div class="mt-3 text-xs font-semibold text-primary opacity-0 selected-indicator transition-opacity">✓ Dipilih</div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SECTION 3: Komponen Menu (dynamic) --}}
                <div id="sec-komponen" class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm hidden">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">3</span>
                        Pilih Menu Komponen
                    </h2>
                    <div id="komponen-container" class="space-y-5"></div>
                </div>

                                {{-- SECTION X: Data Pemesan --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold section-number">4</span>
                        Data Pemesan
                    </h2>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Nama Pemesan <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_pemesan" value="{{ old('nama_pemesan', auth()->user()->name ?? '') }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Nomor Kontak (WhatsApp) <span class="text-red-500">*</span></label>
                            <div class="flex">
                                <span class="inline-flex items-center px-4 font-semibold text-gray-600 bg-gray-50 border border-r-0 border-gray-200 rounded-l-xl">
                                    +62
                                </span>
                                <input type="number" inputmode="numeric" name="kontak" value="{{ old('kontak', auth()->user()->phone_number ?? '') }}" placeholder="81234567890"
                                       class="w-full border border-gray-200 rounded-r-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition [&::-webkit-inner-spin-button]:appearance-none" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-body mb-1">Email Pemesan <span class="text-red-500">*</span></label>
                            <input type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}" placeholder="contoh@gmail.com"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition" required>
                            <p class="text-xs text-gray-500 mt-1">Kami akan mengirimkan detail dan tanda terima ke email ini.</p>
                        </div>
                        <input type="hidden" name="metode_pengiriman" value="delivery">

                        <div id="deliverySection" class="md:col-span-2 mb-4">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-body mb-1">Nama / Alamat Venue (Opsional)</label>
                                <input type="text" name="alamat_venue" value="{{ old('alamat_venue') }}" placeholder="Contoh: Gedung Sabuga / Aula Serbaguna"
                                       class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition mb-3">
                                       
                                <label class="block text-sm font-semibold text-body mb-1">Alamat Lengkap <span class="text-red-500">*</span></label>
                                <textarea name="lokasi_acara" id="alamatDelivery" rows="2"
                                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition" required>{{ old('lokasi_acara', auth()->user()->alamat ?? '') }}</textarea>
                                <p class="text-xs text-body/60 mt-1">💡 Tip: Cari alamat lewat ikon 🔍 di peta, lalu geser pin ke titik yang tepat.</p>
                            </div>

                            {{-- Map Container --}}
                            <div id="map-container" class="rounded-2xl overflow-hidden border border-gray-200 shadow-md mb-3 z-0" style="height: 340px; position:relative;">
                                {{-- Address Card Overlay --}}
                                <div id="map-address-card">
                                    <div class="card-label">📍 Lokasi Venue / Alamat</div>
                                    <div class="card-address" id="cardAlamat">Geser pin ke lokasi kamu...</div>
                                </div>
                                <button type="button" onclick="locateUser()" class="absolute bottom-6 right-4 z-[1000] bg-white w-10 h-10 flex items-center justify-center rounded-xl shadow-lg border border-gray-200 text-primary hover:bg-gray-50 transition" title="Temukan Lokasi Saya">
                                    <i class="ph-bold ph-crosshair text-xl"></i>
                                </button>
                                <div id="map" style="width:100%; height:100%;"></div>
                            </div>
                            
                            <input type="hidden" name="latitude" id="inputLat" value="{{ old('latitude', auth()->user()->latitude ?? '') }}">
                            <input type="hidden" name="longitude" id="inputLng" value="{{ old('longitude', auth()->user()->longitude ?? '') }}">
                            <input type="hidden" name="jarak_km" id="inputJarak">
                            
                            {{-- Jarak Info Card Minimalist --}}
                            <div class="flex flex-col sm:flex-row gap-3 mb-4">
                                <div class="flex-1 bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-500 flex-shrink-0">
                                        <i class="ph ph-storefront text-lg"></i>
                                    </div>
                                    <div>
                                        <p class="text-[10px] text-gray-400 uppercase tracking-wider font-bold">Titik Resto</p>
                                        <p class="text-xs font-bold text-gray-800">Saung Babakan Cinta</p>
                                    </div>
                                </div>
                                <div class="flex-1 bg-white p-3 rounded-xl border border-gray-100 shadow-sm flex items-center gap-3">
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

                        <div class="md:col-span-2 mt-2">
                            <label class="block text-sm font-semibold text-body mb-1">Catatan Tambahan</label>
                            <textarea name="catatan" rows="2"
                                      class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-primary transition">{{ old('catatan') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- SECTION 5: Layanan Tambahan --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">5</span>
                        Layanan Tambahan <span class="text-sm font-normal text-body">(opsional)</span>
                    </h2>
                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach($layananTambahan as $layanan)
                            <label class="flex items-center gap-3 border border-gray-200 rounded-xl px-4 py-3 cursor-pointer hover:border-primary/50 transition">
                                <input type="checkbox" name="layanan_tambahan[]" value="{{ $layanan->id }}"
                                       class="layanan-checkbox w-4 h-4 accent-primary" data-harga="{{ $layanan->harga }}"
                                       {{ in_array($layanan->id, old('layanan_tambahan', [])) ? 'checked' : '' }}>
                                <div>
                                    <p class="text-sm font-semibold text-body">{{ $layanan->nama }}</p>
                                    <p class="text-xs text-secondary">+ Rp {{ number_format($layanan->harga, 0, ',', '.') }}</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- SECTION 6: Opsi Pembayaran --}}
                <div class="bg-surface rounded-2xl border border-primary/10 p-6 mb-6 shadow-sm">
                    <h2 class="text-lg font-serif text-primary mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-primary text-white rounded-full flex items-center justify-center text-sm font-bold">6</span>
                        Opsi Pembayaran
                    </h2>
                    <div class="flex flex-col sm:flex-row gap-4">
                        <label class="flex-1 flex items-center gap-3 border border-primary bg-primary/5 rounded-xl px-4 py-3 cursor-pointer transition">
                            <input type="radio" name="opsi_pembayaran" value="dp" checked class="w-5 h-5 accent-primary" onchange="updatePaymentLabel(this.value)">
                            <div>
                                <p class="text-sm font-semibold text-body">Bayar DP (50%)</p>
                                <p class="text-xs text-secondary">Sisa pelunasan dibayar nanti</p>
                            </div>
                        </label>
                        <label class="flex-1 flex items-center gap-3 border border-gray-200 bg-white rounded-xl px-4 py-3 cursor-pointer hover:border-primary/50 transition">
                            <input type="radio" name="opsi_pembayaran" value="lunas" class="w-5 h-5 accent-primary" onchange="updatePaymentLabel(this.value)">
                            <div>
                                <p class="text-sm font-semibold text-body">Bayar Lunas (100%)</p>
                                <p class="text-xs text-gray-500">Bayar penuh di awal</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- SECTION 7: Ringkasan & Submit --}}
                <div class="bg-primary text-white rounded-2xl p-6 shadow-md">
                    <h2 class="text-lg font-serif mb-4 flex items-center gap-2">
                        <span class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-sm font-bold">7</span>
                        Ringkasan Pesanan
                    </h2>
                    <div class="space-y-2 text-sm mb-4">
                        <div class="flex justify-between">
                            <span class="text-white/70">Subtotal Menu</span>
                            <span id="subtotal-menu" class="font-semibold">Rp 0</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-white/70">Layanan Tambahan</span>
                            <span id="subtotal-addon" class="font-semibold">Rp 0</span>
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
                            <span id="label-payment">DP yang Harus Dibayar <span class="text-white/70 text-xs">(50%)</span></span>
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
        const minDate = "{{ \Carbon\Carbon::today()->addDays(14)->format('Y-m-d') }}";
        let hargaPaket = 0;
        let selectedPaketId = null;
        let metodePengiriman = 'delivery';
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
                // We don't call updateAlamatText here because the user might have saved a custom address string
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

        // Pilih Paket
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
                hargaPaket = parseInt(card.dataset.harga);
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
                div.className = 'border border-gray-100 rounded-xl p-4 bg-canvas';
                
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
                label.innerHTML = 'Total yang Harus Dibayar <span class="text-white/70 text-xs">(100%)</span>';
                amount.textContent = formatRp(currentTotal);
            } else {
                label.innerHTML = 'DP yang Harus Dibayar <span class="text-white/70 text-xs">(50%)</span>';
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
            const porsi = parseInt(document.getElementById('jumlahPorsi').value) || 0;
            const subtotalMenu = hargaPaket * porsi;

            let subtotalAddon = 0;
            let layananAddons = [];
            document.querySelectorAll('.layanan-checkbox:checked').forEach(cb => {
                subtotalAddon += parseInt(cb.dataset.harga);
                layananAddons.push(cb.value);
            });

            document.getElementById('subtotal-menu').textContent = formatRp(subtotalMenu);
            document.getElementById('subtotal-addon').textContent = formatRp(subtotalAddon);
            
            if(!selectedPaketId) return;

            try {
                const res = await fetch("{{ route('pesan.catering.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        paket_id: selectedPaketId,
                        jumlah_porsi: porsi,
                        layanan_tambahan: layananAddons,
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
                    
                    if(data.ongkir === 0 && metodePengiriman === 'delivery' && porsi >= 100) {
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

        document.getElementById('jumlahPorsi').addEventListener('input', hitungTotalPreview);
        document.querySelectorAll('.layanan-checkbox').forEach(cb => cb.addEventListener('change', hitungTotalPreview));

        // Validasi tanggal
        document.getElementById('tanggalAcara').addEventListener('change', function() {
            const warn = document.getElementById('tanggal-warning');
            if (this.value < minDate) {
                warn.classList.remove('hidden');
            } else {
                warn.classList.add('hidden');
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Initialize map immediately because delivery is the only option
            setTimeout(() => {
                initMap();
                map.invalidateSize();
            }, 200);

            hitungTotalPreview();
        });
    </script>
    @endpush
</x-layouts.landing>
