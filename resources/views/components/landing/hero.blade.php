<section id="beranda" class="relative flex items-center overflow-hidden min-h-[85vh] lg:min-h-[calc(100vh-80px)]">
    <img src="{{ asset('images/homepage.webp') }}" alt="Saung Babakan Cinta" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-black/30"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-6 py-16 w-full">
        <div class="max-w-3xl">
            <x-typography.h1 class="!text-white md:!text-3xl lg:!text-4xl drop-shadow-md mb-4 font-light tracking-wide">Saung Babakan Cinta</x-typography.h1>
            <x-typography.p variant="large" class="!text-white/80 mb-8 max-w-xl font-light text-sm md:text-base">Sauyunan dina rasa, babarengan dina carita</x-typography.p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('pesan.catering') }}" class="inline-flex items-center justify-center px-5 py-2 rounded-full bg-white/90 text-black font-medium text-xs hover:bg-white transition-all duration-300 shadow-sm">Pesan Sekarang</a>
                <a href="#menu-dinein" class="inline-flex items-center justify-center px-5 py-2 rounded-full border border-white/20 text-white font-medium text-xs hover:bg-white/10 transition-all duration-300 backdrop-blur-sm">Lihat Menu</a>
            </div>
        </div>
    </div>
</section>
