<div id="deliverySection" class="md:col-span-2 hidden mt-2">
    <div class="rounded-2xl border border-primary/10 bg-primary/[0.02] p-4 sm:p-5 space-y-4">
        <p class="text-xs font-bold text-primary uppercase tracking-wider flex items-center gap-1.5">
            <i class="ph-bold ph-map-pin"></i> Detail Pengiriman
        </p>

        <div>
            <label for="alamatVenue" class="block text-xs font-bold text-body mb-1">Nama Venue / Gedung <span class="text-body/40 font-medium">(Opsional)</span></label>
            <input type="text" id="alamatVenue" name="alamat_venue" value="{{ old('alamat_venue') }}"
                placeholder="Contoh: Gedung Sabuga / Aula Serbaguna"
                class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body placeholder-body/30 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface">
        </div>

        <div>
            <label for="alamatDelivery" class="block text-xs font-bold text-body mb-1">Alamat Pengiriman <span class="text-danger">*</span></label>
            <textarea name="lokasi_acara" id="alamatDelivery" rows="2" placeholder="Contoh: Jl. Dago Asri No. 12, Bandung"
                    class="w-full border border-primary/10 rounded-xl px-3.5 py-2.5 text-sm font-medium text-body placeholder-body/30 transition-all duration-200 focus:border-primary focus:ring-1 focus:ring-primary/20 outline-none bg-surface">{{ old('lokasi_acara') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-semibold text-body mb-1.5">Lokasi Pengiriman di Peta <span class="text-danger">*</span></label>
            <p class="text-xs text-body/60 mb-2">💡 Tip: Cari alamat lewat ikon 🔍 di peta, lalu geser pin ke titik yang tepat.</p>
            <div id="map-container" class="rounded-xl overflow-hidden border border-primary/10 shadow-md mb-3" style="height: 340px; position:relative;">
                <div id="map-address-card">
                    <div class="card-label">📍 Lokasi Pengiriman</div>
                    <div class="card-address" id="cardAlamat">Geser pin ke lokasi kamu...</div>
                </div>

                <div id="map" class="w-full h-full relative"></div>

                <div id="map-center-marker">
                    <x-heroicon-s-map-pin class="w-10 h-10 text-primary" />
                    <div class="marker-shadow"></div>
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