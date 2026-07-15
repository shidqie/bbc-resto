<section id="beranda" class="relative flex items-center overflow-hidden" style="min-height: calc(100vh - 80px);">
    <img src="{{ asset('images/homepage.webp') }}" alt="Saung Babakan Cinta" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-6 py-20 w-full">
        <div class="max-w-xl">
            <h1 class="!text-white text-4xl md:text-5xl leading-tight mb-4 drop-shadow-md">Saung Babakan Cinta</h1>
            <p class="text-white/80 text-base mb-8 leading-relaxed">Hidangan Nusantara otentik dalam suasana saung yang nyaman. Makan di tempat, catering, dan nasi box.</p>
            <div class="flex flex-wrap gap-3">
                <a href="#menu-dinein" class="bg-primary text-white px-5 py-2 rounded-md font-bold text-sm hover:bg-primary-container transition-all shadow-sm">Menu Dine-in</a>
                <a href="{{ route('pesan.catering') }}" class="border border-white/50 text-white px-5 py-2 rounded-md font-bold text-sm hover:bg-white/10 transition-all">Catering</a>
                <a href="{{ route('pesan.nasibox') }}" class="border border-white/50 text-white px-5 py-2 rounded-md font-bold text-sm hover:bg-white/10 transition-all">Nasi Box</a>
            </div>
        </div>
    </div>
</section>
