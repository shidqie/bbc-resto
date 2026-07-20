<x-layouts.member>
    <x-slot:title>Buku Alamat</x-slot:title>

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
        .leaflet-tooltip.address-tooltip {
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
        .leaflet-tooltip.address-tooltip::before {
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

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex items-center gap-3">
            <i class="fa-solid fa-location-dot text-primary text-xl"></i>
            <h1 class="text-2xl font-bold text-gray-900">Buku Alamat</h1>
        </div>

        <div class="p-6">
            @if(session('success'))
                <div class="mb-6 p-4 bg-green-50 text-green-700 rounded-xl flex items-start gap-3">
                    <i class="fa-solid fa-circle-check mt-1"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            
            <p class="text-gray-600 mb-6">Alamat ini akan digunakan sebagai alamat pengiriman *default* saat Anda melakukan pemesanan Nasi Box atau Catering.</p>

            <form action="{{ route('member.alamat.update') }}" method="POST" class="max-w-2xl space-y-5">
                @csrf
                @method('PATCH')

                <div class="mb-4">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                    <textarea name="alamat" id="alamatDelivery" rows="3" placeholder="Contoh: Jl. Merdeka No. 123, Kec. Sumur Bandung, Kota Bandung (Patokan: Depan minimarket)" class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors resize-y" required>{{ old('alamat', $user->alamat) }}</textarea>
                    <p class="text-xs text-body/60 mt-1">💡 Tip: Cari alamat lewat ikon 🔍 di peta, lalu geser pin ke titik yang tepat agar kurir mudah mencari.</p>
                    @error('alamat')
                        <p class="text-danger text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Map Container --}}
                <div id="map-container" class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm mb-5 z-0" style="height: 340px; position:relative;">
                    {{-- Address Card Overlay --}}
                    <div id="map-address-card">
                        <div class="card-label">📍 Lokasi Pin Anda</div>
                        <div class="card-address" id="cardAlamat">Geser pin ke lokasi kamu...</div>
                    </div>
                    <button type="button" onclick="locateUser()" class="absolute bottom-6 right-4 z-[1000] bg-white w-10 h-10 flex items-center justify-center rounded-xl shadow-lg border border-gray-200 text-primary hover:bg-gray-50 transition" title="Temukan Lokasi Saya">
                        <i class="fa-solid fa-crosshairs text-xl"></i>
                    </button>
                    <div id="map" style="width:100%; height:100%;"></div>
                </div>
                
                <input type="hidden" name="latitude" id="inputLat" value="{{ old('latitude', $user->latitude) }}">
                <input type="hidden" name="longitude" id="inputLng" value="{{ old('longitude', $user->longitude) }}">

                <div class="pt-2">
                    <button type="submit" class="px-6 py-3 bg-primary text-white rounded-xl hover:bg-primary/90 font-medium transition-colors shadow-sm shadow-primary/30">
                        Simpan Alamat
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.member>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Init Map
        const defaultLat = {{ old('latitude', $user->latitude) ?: '-6.917464' }};
        const defaultLng = {{ old('longitude', $user->longitude) ?: '107.619123' }};
        const map = L.map('map', { zoomControl: false }).setView([defaultLat, defaultLng], 15);
        L.control.zoom({ position: 'bottomright' }).addTo(map);

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        // Marker (Customer)
        const customIcon = L.divIcon({
            className: 'custom-pin',
            html: `<div style="
                background-color: #DC2626;
                width: 24px;
                height: 24px;
                border-radius: 50% 50% 50% 0;
                transform: rotate(-45deg);
                border: 3px solid white;
                box-shadow: 0 4px 8px rgba(0,0,0,0.3);
            "></div>`,
            iconSize: [24, 24],
            iconAnchor: [12, 24],
            tooltipAnchor: [16, -16]
        });

        let marker = L.marker([defaultLat, defaultLng], {icon: customIcon, draggable: true}).addTo(map);
        marker.bindTooltip("Geser ke lokasi rumah Anda", {permanent: false, direction: "top", className: 'address-tooltip'}).openTooltip();

        // initial label update
        if({{ $user->latitude ? 'true' : 'false' }}) {
            updateAddressLabel(defaultLat, defaultLng);
        }

        // Search Bar (Geocoder)
        const geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            placeholder: "Cari lokasi / jalan...",
            errorMessage: "Tidak ditemukan"
        })
        .on('markgeocode', function(e) {
            const bbox = e.geocode.bbox;
            map.fitBounds(bbox);
            const lat = e.geocode.center.lat;
            const lng = e.geocode.center.lng;
            marker.setLatLng([lat, lng]);
            document.getElementById('inputLat').value = lat;
            document.getElementById('inputLng').value = lng;
            document.getElementById('cardAlamat').textContent = e.geocode.name.split(',').slice(0, 3).join(',');
            
            if(!document.getElementById('alamatDelivery').value) {
                document.getElementById('alamatDelivery').value = e.geocode.name;
            }
        })
        .addTo(map);

        // Drag Marker event
        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('inputLat').value = pos.lat;
            document.getElementById('inputLng').value = pos.lng;
            updateAddressLabel(pos.lat, pos.lng);
        });

        // Click Map event
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            document.getElementById('inputLat').value = e.latlng.lat;
            document.getElementById('inputLng').value = e.latlng.lng;
            updateAddressLabel(e.latlng.lat, e.latlng.lng);
        });

        async function updateAddressLabel(lat, lng) {
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

        window.locateUser = function() {
            if (navigator.geolocation) {
                document.getElementById('cardAlamat').textContent = "Sedang mencari GPS...";
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        map.flyTo([lat, lng], 16);
                        marker.setLatLng([lat, lng]);
                        document.getElementById('inputLat').value = lat;
                        document.getElementById('inputLng').value = lng;
                        updateAddressLabel(lat, lng);
                    },
                    (error) => {
                        alert("Gagal mendapatkan lokasi. Pastikan izin GPS diaktifkan.");
                        document.getElementById('cardAlamat').textContent = "Gagal memuat GPS";
                    }
                );
            } else {
                alert("Browser tidak mendukung geolokasi.");
            }
        }
    });
</script>
