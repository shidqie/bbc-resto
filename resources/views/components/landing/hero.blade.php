<section id="beranda" class="relative flex items-center overflow-hidden" style="min-height: calc(100vh - 64px);">
    <img src="{{ asset('images/homepage.webp') }}" alt="Saung Babakan Cinta" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-6 py-20 w-full">
        <div class="max-w-3xl">
            <x-typography.h1 class="!text-white md:!text-3xl lg:!text-4xl drop-shadow-lg mb-6">Saung Babakan Cinta</x-typography.h1>
            <x-typography.p variant="large" class="!text-white/90 mb-10 max-w-2xl">Hidangan Sunda otentik dalam suasana saung yang nyaman. Makan di tempat, catering, dan nasi box.</x-typography.p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('pesan.catering') }}" class="inline-flex items-center justify-center px-6 py-2.5 rounded-full bg-white text-neutral-900 font-medium text-sm hover:bg-neutral-100 transition-all duration-300">Pesan Sekarang</a>
                <a href="#menu-dinein" class="inline-flex items-center justify-center px-6 py-2.5 rounded-full border border-white/30 text-white font-medium text-sm hover:bg-white/10 transition-all duration-300 backdrop-blur-sm">Lihat Menu</a>
            </div>
        </div>
    </div>
</section>
