<div id="deliverySection" class="md:col-span-2 hidden mt-2">
    <div class="rounded-2xl border border-primary/10 bg-primary/[0.02] p-4 sm:p-5 space-y-4">
        <p class="text-xs font-bold text-primary uppercase tracking-wider flex items-center gap-1.5">
            <x-heroicon-s-map-pin class="w-4 h-4 text-primary" /> Detail Pengiriman
        </p>

        <div>
            <label for="alamatDelivery" class="block text-xs font-bold text-body mb-1">Alamat Pengiriman <span class="text-danger">*</span></label>
            <textarea name="lokasi_acara" id="alamatDelivery" rows="2" placeholder="Contoh: Jl. Dago Asri No. 12, Bandung"
                    class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body placeholder-body/30 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface">{{ old('lokasi_acara') }}</textarea>
        </div>

        <div>
            <label for="alamatVenue" class="block text-xs font-bold text-body mb-1">Nama Venue / Gedung <span class="text-body/40 font-medium">(Opsional)</span></label>
            <input type="text" id="alamatVenue" name="alamat_venue" value="{{ old('alamat_venue') }}"
                placeholder="Contoh: Gedung Sabuga / Aula Serbaguna"
                class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body placeholder-body/30 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface">
        </div>

        <div>
            <label class="block text-sm font-semibold text-body mb-1.5">Lokasi Pengiriman di Peta <span class="text-danger">*</span></label>
            <p class="text-xs text-body/60 mb-2">💡 Tip: Cari alamat lewat bar pencarian, atau geser peta agar pin pas di lokasi Anda.</p>
            <div id="map-container" class="rounded-2xl overflow-hidden border border-primary/20 shadow-sm mb-3 relative z-10" style="height: 380px; isolation: isolate;">
                
                <!-- Custom Top Bar -->
                <div class="absolute top-3 left-3 right-3 z-30 flex gap-2">
                    <div class="flex-1 bg-white rounded-xl shadow-md border border-primary/10 flex items-center px-3 py-2 relative">
                        <x-heroicon-o-magnifying-glass class="w-5 h-5 text-body/40 mr-2 shrink-0" />
                        <input type="text" id="map-search-input" placeholder="Cari lokasi atau alamat..." class="w-full bg-transparent border-none outline-none text-sm font-medium text-body placeholder-body/40" autocomplete="off">
                        <button type="button" id="map-search-btn" class="ml-2 px-3 py-1.5 bg-primary/10 text-primary rounded-lg text-xs font-bold hover:bg-primary hover:text-white transition-colors">Cari</button>
                        
                        <!-- Search Results Dropdown -->
                        <div id="map-search-results" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-xl shadow-xl border border-primary/10 max-h-48 overflow-y-auto hidden flex-col divide-y divide-primary/5 z-40">
                        </div>
                    </div>
                    <button type="button" id="btn-my-location" onclick="locateUser(true)" class="w-11 h-11 bg-white rounded-xl shadow-md border border-primary/10 flex items-center justify-center text-body hover:text-primary hover:bg-primary/5 transition-colors shrink-0" title="Lokasi Saya">
                        <x-heroicon-o-viewfinder-circle class="w-6 h-6" />
                    </button>
                </div>

                <div id="map-address-card" class="absolute bottom-6 left-1/2 -translate-x-1/2 z-30 bg-white rounded-xl shadow-xl border border-primary/10 px-5 py-2.5 text-center min-w-[240px] max-w-[90%] pointer-events-none transition-all">
                    <div class="text-[10px] font-bold text-body/40 uppercase tracking-widest mb-0.5">📍 Titik Pengiriman</div>
                    <div class="text-xs font-bold text-body leading-tight" id="cardAlamat">Geser peta untuk menentukan lokasi...</div>
                </div>

                <div id="map" class="w-full h-full relative z-[1]"></div>

                <!-- Custom Center Marker Overlay -->
                <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-30 pointer-events-none flex flex-col items-center pb-8 transition-transform duration-200" id="center-marker">
                    <div class="bg-primary text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-md mb-1.5 relative whitespace-nowrap animate-bounce">
                        Pilih Lokasi Ini
                        <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-2 h-2 bg-primary transform rotate-45"></div>
                    </div>
                    <div class="w-10 h-10 bg-surface shadow-lg rounded-full flex items-center justify-center text-primary border-4 border-primary/20 relative">
                        <x-heroicon-s-map-pin class="w-6 h-6 text-primary" />
                        <div class="absolute -bottom-2 w-4 h-1 bg-black/20 rounded-full blur-[2px]"></div>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="latitude" id="inputLat" value="{{ old('latitude', auth()->user()->latitude ?? '') }}">
        <input type="hidden" name="longitude" id="inputLng" value="{{ old('longitude', auth()->user()->longitude ?? '') }}">
        <input type="hidden" name="jarak_km" id="inputJarak">

        <div class="bg-primary/[0.04] border border-primary/10 rounded-xl p-3.5 flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center flex-shrink-0 text-primary">
                <x-heroicon-o-truck class="w-4 h-4" />
            </div>
            <div>
                <p class="text-[10px] text-primary font-bold uppercase tracking-wider mb-0.5">Jarak & Ongkir</p>
                <p class="text-sm font-bold text-body" id="textJarak">– km</p>
            </div>
        </div>
        <p id="jarakWarning" class="text-danger text-xs mt-2 hidden"></p>
    </div>
</div>