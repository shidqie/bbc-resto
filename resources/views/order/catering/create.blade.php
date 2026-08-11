<x-layouts.landing>
    <x-slot:title>Pesan Katering — Saung Babakan Cinta</x-slot:title>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <style>
        /* Custom Geocoder Search Bar (Google Maps Style) */
        .leaflet-top.leaflet-right {
            top: 10px !important;
            right: 10px !important;
            left: 10px !important;
            display: flex !important;
            justify-content: center !important;
            pointer-events: none !important;
            z-index: 1000 !important;
        }
        .leaflet-control-geocoder {
            pointer-events: auto !important;
            width: 90% !important;
            max-width: 360px !important;
            margin: 0 !important;
            border-radius: 12px !important;
            box-shadow: 0 4px 14px rgba(0,0,0,0.12) !important;
            border: 1px solid #E5E7EB !important;
            background: white !important;
            overflow: hidden !important;
        }
        .leaflet-control-geocoder-form {
            display: flex !important;
            align-items: center !important;
            width: 100% !important;
        }
        .leaflet-control-geocoder-form input {
            border: none !important;
            padding: 8px 12px !important;
            font-size: 12px !important;
            font-weight: 500 !important;
            width: 100% !important;
            background: transparent !important;
            color: #111827 !important;
            outline: none !important;
        }
        .leaflet-control-geocoder-form input:focus {
            outline: none !important;
            box-shadow: none !important;
        }
        .leaflet-control-geocoder-icon {
            background-color: transparent !important;
            border-radius: 0 !important;
            width: 36px !important;
            height: 36px !important;
            background-size: 18px 18px !important;
            opacity: 0.6;
            flex-shrink: 0;
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
            bottom: 16px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            background: white;
            border-radius: 12px;
            padding: 10px 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            min-width: 260px;
            max-width: 88%;
            text-align: center;
            pointer-events: none;
            border: 1px solid #E5E7EB;
        }
        #map-address-card .card-label {
            font-size: 10px;
            color: #6B7280;
            font-weight: 700;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        #map-address-card .card-address {
            font-size: 12px;
            font-weight: 700;
            color: #111827;
            line-height: 1.4;
        }
    </style>
    <section class="py-16 bg-white min-h-screen">
        <div class="max-w-7xl mx-auto px-4">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-10">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Form Pemesanan Katering</h1>
                    <p class="text-gray-500 text-sm">Harap melakukan pemesanan minimal H-4 sebelum acara (DP 50%)</p>
                </div>
                <a href="{{ url('/') }}" class="text-gray-500 hover:text-gray-700 text-sm font-medium flex items-center gap-1">
                    <x-heroicon-o-x-mark class="w-4 h-4" />
                    Batalkan Pesan
                </a>
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
                <div class="grid lg:grid-cols-3 gap-12 items-start">
                    
                    {{-- LEFT COLUMN: Form Input --}}
                    <div class="lg:col-span-2 divide-y divide-gray-100">
                        
                        {{-- SECTION 1: Data Pemesan --}}
                        <div class="pb-6">
                            <h2 class="text-base font-bold text-gray-900 mb-4">1. Data Pemesan</h2>
                            <div class="grid md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Pemesan / Instansi <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama_pemesan" value="{{ old('nama_pemesan', optional(auth('pelanggan')->user())->nama ?? '') }}"
                                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/20 outline-none bg-white" required>
                                </div>
                                <div>
                                    <x-input-wa name="kontak" label="Nomor Telepon / WhatsApp" :value="optional(auth('pelanggan')->user())->nomor_telepon ?? ''" :required="true" />
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 2: Detail Acara --}}
                        <div class="py-6">
                            <h2 class="text-base font-bold text-gray-900 mb-3">2. Detail Acara</h2>
                            <div class="grid md:grid-cols-2 gap-4 items-start">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Acara <span class="text-red-500">*</span></label>
                                    <input type="date" name="tanggal_acara" id="tanggalAcara"
                                           min="{{ \Carbon\Carbon::today()->addDays(4)->format('Y-m-d') }}"
                                           value="{{ old('tanggal_acara') }}"
                                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-900 focus:outline-none focus:ring-1 focus:ring-[#0D3024] focus:border-[#0D3024] transition bg-white" required>
                                    <p id="tanggal-warning" class="text-red-500 text-xs mt-1 hidden">Pemesanan catering minimal H-4 sebelum acara.</p>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Acara <span class="text-red-500">*</span></label>
                                    <input type="time" name="jam_acara" id="jamAcara"
                                           value="{{ old('jam_acara') }}"
                                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-900 focus:outline-none focus:ring-1 focus:ring-[#0D3024] focus:border-[#0D3024] transition bg-white" required>
                                </div>
                                <div class="md:col-span-2">
                                    <x-ui.input-qty id="jumlahPorsi" name="jumlah_porsi" label="Jumlah Porsi" :value="old('jumlah_porsi', 50)" :required="true" min="50" />
                                    <p id="jumlah-warning" class="text-red-500 text-xs mt-1 hidden">Minimal order 50 porsi.</p>
                                </div>
                                
                                <div class="md:col-span-2 mt-2 border-t border-gray-100 pt-3">
                                    <label class="block text-xs font-bold text-gray-700 mb-2">Metode Pengiriman <span class="text-red-500">*</span></label>
                                    <div class="flex gap-4 mb-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="metode_pengiriman" value="pickup" class="metode-radio w-4 h-4 text-[#0D3024] focus:ring-[#0D3024]/20" checked>
                                            <span class="text-xs font-medium text-gray-900">Diambil (Pickup)</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" name="metode_pengiriman" value="delivery" class="metode-radio w-4 h-4 text-[#0D3024] focus:ring-[#0D3024]/20">
                                            <span class="text-xs font-medium text-gray-900">Diantar (Delivery)</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Jam Pengambilan / Pengantaran <span class="text-red-500">*</span></label>
                                    <input type="time" name="jam_pengambilan" id="jamPengambilan"
                                           value="{{ old('jam_pengambilan') }}"
                                           class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-900 focus:outline-none focus:ring-1 focus:ring-[#0D3024] focus:border-[#0D3024] transition bg-white" required>
                                </div>

                                <div id="deliverySection" class="md:col-span-2 hidden mt-2">
                                    <div class="mb-3 space-y-3">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Venue / Gedung (Opsional)</label>
                                            <input type="text" name="alamat_venue" value="{{ old('alamat_venue') }}" placeholder="Contoh: Gedung Sabuga / Aula Serbaguna"
                                                class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/20 outline-none bg-white">
                                        </div>
                                            
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Pengantaran <span class="text-red-500">*</span></label>
                                            <textarea name="lokasi_acara" id="alamatDelivery" rows="2"
                                                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/20 outline-none bg-white">{{ old('lokasi_acara') }}</textarea>
                                        </div>
                                    </div>

                                    {{-- Map Container --}}
                                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Lokasi Pengantaran di Peta <span class="text-red-500">*</span></label>
                                    <p class="text-xs text-body/60 mb-2">💡 Tip: Cari alamat lewat ikon 🔍 di peta, lalu geser pin ke titik yang tepat.</p>
                                    <div id="map-container" class="rounded-xl overflow-hidden border border-gray-200 shadow-md mb-3 z-0" style="height: 340px; position:relative;">
                                        {{-- Address Card Overlay --}}
                                        <div id="map-address-card">
                                            <div class="card-label">📍 Lokasi Pengantaran</div>
                                            <div class="card-address" id="cardAlamat">Geser pin ke lokasi kamu...</div>
                                        </div>

                                        <div id="map" class="w-full h-full z-0 relative"></div>
                                        
                                        <div id="map-center-marker">
                                            <x-heroicon-s-map-pin class="w-10 h-10 text-[#0D3024]" />
                                            <div class="marker-shadow"></div>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="latitude" id="inputLat" value="{{ old('latitude', auth()->user()->latitude ?? '') }}">
                                    <input type="hidden" name="longitude" id="inputLng" value="{{ old('longitude', auth()->user()->longitude ?? '') }}">
                                    <input type="hidden" name="jarak_km" id="inputJarak">

                                    <div class="bg-blue-50/50 border border-blue-100 rounded-xl p-3 flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0 text-blue-600">
                                            <x-heroicon-o-truck class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider mb-0.5">Jarak & Ongkir</p>
                                            <p class="text-xs font-bold text-gray-800" id="textJarak">– km</p>
                                        </div>
                                    </div>
                                    <p id="jarakWarning" class="text-red-500 text-xs mt-2 hidden"></p>
                                </div>
                            </div>
                        </div>

                        {{-- SECTION 3: Pilih Paket Katering --}}
                        <div class="py-6">
                            <h2 class="text-base font-bold text-gray-900 mb-3">3. Pilih Paket Katering</h2>
                            <div class="grid md:grid-cols-2 gap-4">
                                @foreach($pakets as $paket)
                                    <label class="paket-card cursor-pointer border rounded-2xl p-4 transition-all duration-200 hover:border-[#0D3024] hover:shadow-xs border-gray-200 bg-white relative flex flex-col justify-between"
                                           data-paket-id="{{ $paket->id }}" data-harga="{{ $paket->harga_jual }}">
                                        <input type="radio" name="paket_id" value="{{ $paket->id }}" class="sr-only paket-radio" {{ old('paket_id') == $paket->id ? 'checked' : '' }} required>
                                        <div>
                                            @if($paket->foto)
                                                <img src="{{ Storage::url($paket->foto) }}" alt="{{ $paket->nama_menu }}" class="w-full h-36 object-cover rounded-xl mb-3">
                                            @else
                                                <div class="w-full h-36 rounded-xl bg-gray-100 flex items-center justify-center mb-3 text-gray-300">
                                                    <i class="ph ph-package text-3xl"></i>
                                                </div>
                                            @endif
                                            <div class="mb-2">
                                                <h3 class="text-sm font-bold text-gray-900 leading-tight mb-1">{{ $paket->nama_menu }}</h3>
                                                <span class="text-xs font-bold text-[#0D3024]">Rp {{ number_format($paket->harga_jual, 0, ',', '.') }} <span class="text-[11px] font-normal text-gray-500">/porsi</span></span>
                                            </div>
                                            <p class="text-gray-500 text-xs line-clamp-2 mb-3">{{ $paket->deskripsi }}</p>
                                            <ul class="text-xs text-gray-600 space-y-1 mb-3">
                                                @foreach($paket->komponen_paket->sortBy('urutan') as $komp)
                                                    <li class="flex items-center gap-1.5">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $komp->tipe_komponen === 'tetap' ? 'bg-[#0D3024]' : 'bg-amber-500' }} flex-shrink-0"></span>
                                                        <span>{{ $komp->nama_komponen }}
                                                            @if($komp->tipe_komponen === 'pilihan')<span class="text-amber-600 text-[11px] font-medium">(pilih 1)</span>@endif
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="mt-auto pt-2">
                                            <div class="text-[11px] font-bold bg-[#0D3024] text-white px-2.5 py-1 rounded-full w-max opacity-0 selected-indicator transition-opacity">✓ Dipilih</div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- SECTION 4: Pilih Menu --}}
                        <div id="sec-komponen" class="py-6 hidden">
                            <h2 class="text-base font-bold text-gray-900 mb-3">4. Pilih Item Menu</h2>
                            <div id="komponen-container" class="space-y-4 mb-4"></div>
                            
                            <div class="mt-4 pt-4 border-t border-gray-100">
                                <label class="block text-xs font-bold text-gray-700 mb-1.5">Catatan Tambahan</label>
                                <textarea name="catatan" rows="2"
                                          class="w-full border border-gray-200 rounded-xl px-3.5 py-2 text-xs font-medium text-gray-900 placeholder-gray-300 transition-all duration-200 focus:border-[#0D3024] focus:ring-1 focus:ring-[#0D3024]/20 outline-none bg-white">{{ old('catatan') }}</textarea>
                            </div>
                        </div>

                        {{-- SECTION 5: Pembayaran --}}
                        <div class="py-6">
                            <h2 class="text-base font-bold text-gray-900 mb-3">5. Pembayaran</h2>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <label class="flex-1 flex items-center gap-3 border border-[#0D3024] bg-[#0D3024]/5 rounded-xl px-3.5 py-2.5 cursor-pointer transition-all duration-200">
                                    <input type="radio" name="opsi_pembayaran" value="dp" checked class="w-4 h-4 text-[#0D3024] focus:ring-[#0D3024]/20" onchange="updatePaymentLabel(this.value)">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Bayar DP (50%)</p>
                                        <p class="text-[11px] text-gray-500 font-medium">Sisa dibayar H-4 sebelum acara</p>
                                    </div>
                                </label>
                                <label class="flex-1 flex items-center gap-3 border border-gray-200 bg-white rounded-xl px-3.5 py-2.5 cursor-pointer hover:border-[#0D3024]/30 transition-all duration-200">
                                    <input type="radio" name="opsi_pembayaran" value="lunas" class="w-4 h-4 text-[#0D3024] focus:ring-[#0D3024]/20" onchange="updatePaymentLabel(this.value)">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">Bayar Lunas (100%)</p>
                                        <p class="text-[11px] text-gray-500 font-medium">Selesaikan pembayaran sekaligus</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div> {{-- END LEFT COLUMN --}}

                    {{-- RIGHT COLUMN: Ringkasan & Submit (Sticky) --}}
                    <div class="lg:col-span-1 sticky top-28">
                        {{-- SECTION 6: Ringkasan & Submit --}}
                        <div class="bg-white border border-gray-200 rounded-xl shadow-xs flex flex-col max-h-[calc(100vh-8rem)]">
                            <div class="p-5 overflow-y-auto custom-scrollbar flex-1">
                            <h2 class="text-sm font-bold text-gray-900 mb-1">
                                Ringkasan Pesanan
                            </h2>
                            <p class="text-[11px] text-gray-500 mb-4 pb-3 border-b border-gray-100">Periksa kembali detail pesanan sebelum melanjutkan pembayaran.</p>
                            
                            {{-- DETAIL PESANAN --}}
                            <div class="mb-4">
                                <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Detail Pesanan</h3>
                                <div class="space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Jenis Pesanan</span>
                                        <span class="font-semibold text-gray-900">Katering</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Paket</span>
                                        <span id="summary-paket" class="font-semibold text-gray-900 text-right max-w-[140px] truncate">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Jumlah Pesanan</span>
                                        <span id="summary-porsi" class="font-semibold text-gray-900">0 Porsi</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Tanggal Acara</span>
                                        <span id="summary-tgl-acara" class="font-semibold text-gray-900">-</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Jam Acara</span>
                                        <span id="summary-jam-acara" class="font-semibold text-gray-900">-</span>
                                    </div>
                                </div>
                            </div>

                            {{-- PENERIMAAN PESANAN --}}
                            <div class="mb-4 pt-3 border-t border-gray-100">
                                <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">PENGIRIMAN PESANAN</h3>
                                <div class="space-y-1.5 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Metode Pengiriman</span>
                                        <span id="summary-metode" class="font-semibold text-gray-900">Diambil (Pickup)</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500" id="summary-jam-kirim-label">Jam Ambil</span>
                                        <span id="summary-jam-kirim" class="font-semibold text-gray-900">-</span>
                                    </div>
                                    <div class="flex justify-between" id="summary-alamat-row" style="display: none;">
                                        <span class="text-gray-500 whitespace-nowrap mr-4">Alamat</span>
                                        <span id="summary-alamat" class="font-semibold text-gray-900 text-right line-clamp-2">-</span>
                                    </div>
                                    <div class="flex justify-between" id="summary-jarak-row" style="display: none;">
                                        <span class="text-gray-500">Jarak Pengantaran</span>
                                        <span id="summary-jarak" class="font-semibold text-gray-900">-</span>
                                    </div>
                                </div>
                            </div>

                            {{-- RINCIAN PEMBAYARAN --}}
                            <div class="mb-4 pt-3 border-t border-gray-100">
                                <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-2">Rincian Pembayaran</h3>
                                <div class="space-y-1.5 text-xs mb-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-500 font-medium">Subtotal Menu</span>
                                        <span id="subtotal-menu" class="font-bold text-gray-900">Rp 0</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-gray-500 font-medium" id="summary-ongkir-label">Biaya Pengantaran</span>
                                            <span id="badge-gratis-ongkir" class="hidden px-2 py-0.5 bg-emerald-100 text-emerald-700 border border-emerald-200 rounded text-[10px] font-bold uppercase tracking-wider">Gratis Ongkir</span>
                                        </div>
                                        <div class="text-right">
                                            <span id="summary-ongkir-coret" class="hidden text-[10px] text-gray-400 line-through mr-1 block sm:inline"></span>
                                            <span id="summary-ongkir" class="font-bold text-gray-900">Rp 0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="border-t border-gray-100 border-dashed pt-2 flex justify-between items-center text-xs">
                                    <span class="font-bold text-gray-900">Total Tagihan</span>
                                    <span id="total-tagihan" class="font-bold text-gray-900 text-sm">Rp 0</span>
                                </div>
                            </div>

                            {{-- DP DAN SISA --}}
                            <div class="mb-4">
                                <div class="flex justify-between items-center text-xs bg-amber-50 rounded-t-xl p-3 border border-amber-200/60 border-b-0">
                                    <span id="label-payment" class="text-amber-900 font-bold">DP Pembayaran <span class="text-amber-700/70 text-[10px] font-normal">(50%)</span></span>
                                    <span id="dp-amount" class="font-bold text-amber-700 text-sm">Rp 0</span>
                                </div>
                                <div id="sisa-pelunasan-container" class="flex justify-between items-center text-xs bg-gray-50 rounded-b-xl p-3 border border-gray-200/60">
                                    <span class="text-gray-600 font-bold">Sisa Pelunasan</span>
                                    <span id="summary-sisa-pelunasan" class="font-bold text-gray-800">Rp 0</span>
                                </div>
                            </div>
                            
                            {{-- BATAS WAKTU --}}
                            <div class="mb-4 p-3 bg-blue-50/50 border border-blue-100 rounded-xl">
                                <div class="flex justify-between items-center text-xs mb-1">
                                    <span class="text-blue-800 font-bold">Batas Pelunasan</span>
                                    <span id="summary-batas-pelunasan" class="font-bold text-blue-900">-</span>
                                </div>
                                <p class="text-[9px] text-blue-700 leading-relaxed">Pelunasan wajib dilakukan paling lambat H-4 sebelum tanggal pengambilan atau pengantaran pesanan.</p>
                            </div>

                            <div class="mb-5 p-3.5 bg-gray-50 border border-gray-200 rounded-xl text-[10px] text-gray-600 space-y-1.5 leading-relaxed">
                                <p class="font-bold text-gray-800 mb-1.5">Syarat & Ketentuan Katering:</p>
                                <ul class="list-disc pl-3 space-y-1 text-[9px]">
                                    <li>Konsumen wajib membayar uang muka (DP) sebesar 50% dari total nilai pesanan katering.</li>
                                    <li>Pembayaran dapat dilakukan melalui transfer ke rekening yang telah ditentukan oleh pihak Rumah Makan Saung Babakan Cinta.</li>
                                    <li>Apabila hingga batas waktu H-4 konsumen belum melakukan pelunasan, maka pesanan dianggap batal.</li>
                                    <li>DP yang telah dibayarkan dan diterima tidak dapat dikembalikan apabila pesanan dibatalkan oleh konsumen atau dibatalkan karena tidak dilakukan pelunasan sampai batas waktu yang ditentukan.</li>
                                </ul>
                            </div>

                            </div>
                            
                            {{-- STICKY SUBMIT BUTTON --}}
                            <div class="p-5 border-t border-gray-100 bg-white rounded-b-xl shrink-0">
                                <button type="submit" id="submitBtn"
                                        class="w-full bg-[#0D3024] hover:bg-[#1a4a35] text-white font-semibold text-xs py-2.5 rounded-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.99]">
                                    Lanjut Pembayaran
                                </button>
                            </div>
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
        const minDate = "{{ \Carbon\Carbon::today()->addDays(4)->format('Y-m-d') }}";
        let hargaPaket = 0;
        let selectedPaketId = null;
        let metodePengiriman = 'pickup';
        let jarakKm = 0;

        // Peta & Jarak
        const bbcLat = -6.8244057;
        const bbcLng = 107.5289353;
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
                placeholder: 'Cari lokasi atau alamat...',
                collapsed: false,
                position: 'topright'
            }).addTo(map);

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
                        window.showToast('error', "Gagal mendeteksi lokasi. Pastikan izin lokasi (GPS) diaktifkan di browser.");
                        document.getElementById('cardAlamat').textContent = "Geser pin ke lokasi kamu...";
                    }
                }, { enableHighAccuracy: true });
            } else if (showAlert) {
                window.showToast('error', "Browser Anda tidak mendukung fitur lokasi GPS.");
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
                    
                    document.getElementById('jarakWarning').classList.add('hidden');
                    document.getElementById('submitBtn').disabled = false;
                    hitungTotalPreview(); // Update ongkir via ajax
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
                    document.getElementById('alamatDelivery').required = true;
                    document.getElementById('alamatDelivery').name = 'lokasi_acara';
                    setTimeout(initMap, 200);
                } else {
                    document.getElementById('deliverySection').classList.add('hidden');
                    document.getElementById('alamatDelivery').required = false;
                    document.getElementById('alamatDelivery').name = '';
                }
                hitungTotalPreview();
            });
        });

        // Pilih Paket
        document.querySelectorAll('.paket-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.paket-card').forEach(c => {
                    c.classList.remove('border-[#0D3024]', 'bg-[#0D3024]/5', 'ring-1', 'ring-[#0D3024]');
                    c.classList.add('border-gray-200', 'bg-white');
                    c.querySelector('.selected-indicator').style.opacity = '0';
                });
                card.classList.add('border-[#0D3024]', 'bg-[#0D3024]/5', 'ring-1', 'ring-[#0D3024]');
                card.classList.remove('border-gray-200', 'bg-white');
                card.querySelector('.selected-indicator').style.opacity = '1';
                card.querySelector('.paket-radio').checked = true;
                
                selectedPaketId = card.dataset.paketId;
                hargaPaket = parseInt(card.dataset.harga);
                
                // Update Paket summary name
                const pName = card.querySelector('div h3') ? card.querySelector('div h3').textContent : 'Paket Terpilih';
                document.getElementById('summary-paket').textContent = pName;
                
                loadKomponen(selectedPaketId);
                hitungTotalPreview();
            });
        });

        async function loadKomponen(paketId) {
            // ... (keep the same structure, updating UI with JS listener for dates)
            const sec = document.getElementById('sec-komponen');
            const container = document.getElementById('komponen-container');
            sec.classList.remove('hidden');
            container.innerHTML = '<p class="text-gray-500 text-xs font-medium">Memuat komponen menu...</p>';

            const res = await fetch(`/pesan/catering/komponen/${paketId}`);
            const komponens = await res.json();

            container.innerHTML = '';
            komponens.forEach(komp => {
                const div = document.createElement('div');
                div.className = 'border border-gray-200/80 rounded-xl p-3.5 bg-gray-50/50';
                
                if (komp.tipe === 'fixed') {
                    div.innerHTML = `
                        <p class="text-xs font-bold text-gray-800 mb-2">${komp.nama_komponen}</p>
                        <div class="flex flex-wrap gap-2">
                            ${komp.opsi.map(o => `
                                <div class="flex items-center gap-1.5 px-3 py-1 bg-[#0D3024]/10 text-[#0D3024] text-xs rounded-lg font-semibold">
                                    ${o.menu.foto ? `<img src="/storage/${o.menu.foto}" alt="${o.menu.nama}" class="w-4 h-4 rounded-full object-cover">` : ''}
                                    <span>${o.menu.nama} ✓</span>
                                </div>
                            `).join('')}
                        </div>`;
                } else {
                    div.innerHTML = `
                        <p class="text-xs font-bold text-gray-800 mb-2">${komp.nama_komponen} <span class="text-amber-600 font-medium text-[11px]">(pilih 1)</span></p>
                        <div class="flex flex-wrap gap-2">
                            ${komp.opsi.map(o => `
                                <label class="cursor-pointer group relative">
                                    <input type="radio" name="komponen[${komp.id}]" value="${o.menu.id}" class="opacity-0 absolute w-0 h-0 peer" required>
                                    <div class="flex items-center gap-1.5 px-3 py-1.5 border border-gray-200 bg-white rounded-xl font-medium text-gray-800 text-xs peer-checked:bg-[#0D3024] peer-checked:border-[#0D3024] peer-checked:text-white transition-all duration-200 group-hover:border-[#0D3024]/50">
                                        ${o.menu.foto ? `<img src="/storage/${o.menu.foto}" alt="${o.menu.nama}" class="w-4 h-4 rounded-full object-cover">` : ''}
                                        <span>${o.menu.nama}</span>
                                    </div>
                                </label>`).join('')}
                        </div>`;
                }
                container.appendChild(div);
            });
        }

        function formatRp(n) {
            return 'Rp ' + Math.round(n || 0).toLocaleString('id-ID');
        }

        let currentTotal = 0;
        let currentDp = 0;

        function updatePaymentLabel(val) {
            const label = document.getElementById('label-payment');
            const amount = document.getElementById('dp-amount');
            if(val === 'lunas') {
                label.innerHTML = 'Bayar Lunas <span class="text-amber-700/70 text-[11px] font-normal">(100%)</span>';
                amount.textContent = formatRp(currentTotal);
            } else {
                label.innerHTML = 'DP Pembayaran <span class="text-amber-700/70 text-[11px] font-normal">(50%)</span>';
                amount.textContent = formatRp(currentDp);
            }
            
            // update styling borders
            document.querySelectorAll('input[name="opsi_pembayaran"]').forEach(el => {
                const parent = el.closest('label');
                if(el.checked) {
                    parent.classList.add('border-[#0D3024]', 'bg-[#0D3024]/5');
                    parent.classList.remove('border-gray-200', 'bg-white');
                } else {
                    parent.classList.remove('border-[#0D3024]', 'bg-[#0D3024]/5');
                    parent.classList.add('border-gray-200', 'bg-white');
                }
            });
        }

        async function hitungTotalPreview() {
            const porsi = parseInt(document.getElementById('jumlahPorsi').value) || 0;
            const subtotalMenu = hargaPaket * porsi;

            document.getElementById('subtotal-menu').textContent = formatRp(subtotalMenu);
            document.getElementById('summary-porsi').textContent = porsi > 0 ? porsi + ' Porsi' : '0 Porsi';
            
            // Format Dates & Times for summary
            const tglAcara = document.getElementById('tanggalAcara').value;
            if(tglAcara) {
                const dateObj = new Date(tglAcara);
                document.getElementById('summary-tgl-acara').textContent = dateObj.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
                
                // Calculate Batas Pelunasan (H-4)
                const batas = new Date(dateObj);
                batas.setDate(batas.getDate() - 4);
                document.getElementById('summary-batas-pelunasan').textContent = batas.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
            }
            document.getElementById('summary-jam-acara').textContent = document.getElementById('jamAcara').value || '-';
            
            document.getElementById('summary-jam-kirim').textContent = document.getElementById('jamPengambilan').value || '-';

            if(!selectedPaketId) return;

            try {
                const res = await fetch("{{ route('pesan.catering.preview') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        paket_id: selectedPaketId,
                        jumlah_porsi: porsi,
                        layanan_tambahan: [],
                        metode_pengiriman: metodePengiriman,
                        jarak_km: jarakKm
                    })
                });
                const data = await res.json();
                
                if(res.ok) {
                    if (metodePengiriman === 'delivery') {
                        document.getElementById('summary-jarak-row').style.display = 'flex';
                        document.getElementById('summary-alamat-row').style.display = 'flex';
                        document.getElementById('summary-jarak').textContent = jarakKm ? jarakKm.toFixed(2) + ' km' : '0 km';
                        document.getElementById('summary-alamat').textContent = document.getElementById('alamatDelivery').value || '-';
                        document.getElementById('summary-ongkir-label').textContent = 'Biaya Pengantaran';
                        document.getElementById('summary-metode').textContent = 'Diantar (Delivery)';
                        document.getElementById('summary-jam-kirim-label').textContent = 'Jam Pengantaran';
                    } else {
                        document.getElementById('summary-jarak-row').style.display = 'none';
                        document.getElementById('summary-alamat-row').style.display = 'none';
                        document.getElementById('summary-ongkir-label').textContent = 'Metode';
                        document.getElementById('summary-ongkir').textContent = 'Diambil';
                        document.getElementById('summary-metode').textContent = 'Diambil (Pickup)';
                        document.getElementById('summary-jam-kirim-label').textContent = 'Jam Ambil';
                    }
                    
                    if (metodePengiriman === 'delivery') {
                        document.getElementById('summary-ongkir').textContent = formatRp(data.ongkir || 0);
                        
                        // Tampilkan badge gratis ongkir jika pesanan memenuhi syarat (>= 100 porsi)
                        if (data.ongkir_normal && data.ongkir < data.ongkir_normal) {
                            document.getElementById('summary-ongkir-coret').textContent = formatRp(data.ongkir_normal);
                            document.getElementById('summary-ongkir-coret').classList.remove('hidden');
                            
                            document.getElementById('badge-gratis-ongkir').classList.remove('hidden');
                            if (data.ongkir === 0) {
                                document.getElementById('badge-gratis-ongkir').textContent = 'Gratis Ongkir';
                            } else {
                                document.getElementById('badge-gratis-ongkir').textContent = 'Diskon Ongkir';
                            }
                        } else {
                            document.getElementById('summary-ongkir-coret').classList.add('hidden');
                            document.getElementById('badge-gratis-ongkir').classList.add('hidden');
                        }
                    } else {
                        document.getElementById('summary-ongkir-coret').classList.add('hidden');
                        document.getElementById('badge-gratis-ongkir').classList.add('hidden');
                    }
                    
                    document.getElementById('total-tagihan').textContent = formatRp(data.total);
                    currentTotal = data.total;
                    currentDp = data.dp;
                    
                    document.getElementById('summary-sisa-pelunasan').textContent = formatRp(data.total - data.dp);
                    
                    const opsiBayar = document.querySelector('input[name="opsi_pembayaran"]:checked').value;
                    updatePaymentLabel(opsiBayar);
                    if(opsiBayar === 'lunas') {
                         document.getElementById('sisa-pelunasan-container').style.display = 'none';
                         document.getElementById('submitBtn').textContent = 'Bayar Lunas ' + formatRp(data.total);
                    } else {
                         document.getElementById('sisa-pelunasan-container').style.display = 'flex';
                         document.getElementById('submitBtn').textContent = 'Bayar DP ' + formatRp(data.dp);
                    }
                    
                    // Tier gratis ongkir akan otomatis menjadi Rp 0 dari server
                    checkFormValidity();
                } else {
                    window.showToast('error', data.error || "Gagal menghitung tagihan.");
                    document.getElementById('submitBtn').disabled = true;
                }
            } catch(e) {
                console.error(e);
            }
        }
        
        // Add event listeners to update summary on input
        document.getElementById('tanggalAcara').addEventListener('change', hitungTotalPreview);
        document.getElementById('jamAcara').addEventListener('input', hitungTotalPreview);
        document.getElementById('jamPengambilan').addEventListener('input', hitungTotalPreview);
        document.getElementById('alamatDelivery').addEventListener('input', hitungTotalPreview);
        document.querySelectorAll('input[name="opsi_pembayaran"]').forEach(el => el.addEventListener('change', hitungTotalPreview));

        function checkFormValidity() {
            const form = document.getElementById('cateringForm');
            const submitBtn = document.getElementById('submitBtn');
            
            let isValid = form.checkValidity();
            
            const porsi = parseInt(document.getElementById('jumlahPorsi').value) || 0;
            if(porsi < 50) isValid = false;
            if(!selectedPaketId) isValid = false;

            const metodePengiriman = document.querySelector('input[name="metode_pengiriman"]:checked').value;
            if (metodePengiriman === 'delivery') {
                const alamat = document.getElementById('alamatDelivery').value;
                if (!alamat || alamat.trim() === '') isValid = false;
            }

            submitBtn.disabled = !isValid;
        }

        document.querySelectorAll('#cateringForm input, #cateringForm select, #cateringForm textarea').forEach(el => {
            el.addEventListener('input', checkFormValidity);
            el.addEventListener('change', checkFormValidity);
        });

        document.getElementById('jumlahPorsi').addEventListener('input', function() {
            const warn = document.getElementById('jumlah-warning');
            const jumlah = parseInt(this.value);
            if (jumlah < 50) {
                warn.classList.remove('hidden');
                warn.textContent = "Minimal order 50 porsi.";
            } else if (metodePengiriman === 'delivery' && jumlah < 50) {
                // Not applicable since min is 50, but we can leave it as logic
                warn.classList.remove('hidden');
                warn.textContent = "Minimal order 50 porsi untuk Delivery. Ubah ke Pickup.";
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

        // Auto-select paket dari URL parameter
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const paketId = urlParams.get('paket_id');
            if (paketId) {
                const paketCard = document.querySelector(`.paket-card[data-paket-id="${paketId}"]`);
                if (paketCard) {
                    paketCard.click();
                }
            }
        });
    </script>
    @endpush
</x-layouts.landing>