<section id="beranda" class="relative flex items-center overflow-hidden min-h-[85vh] lg:min-h-[calc(100vh-80px)]">
    <img src="{{ asset('images/homepage.webp') }}" alt="Saung Babakan Cinta" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out dark:opacity-0">
    <img src="{{ asset('images/homepage_malam.webp') }}" alt="Saung Babakan Cinta" class="absolute inset-0 w-full h-full object-cover transition-opacity duration-1000 ease-in-out opacity-0 dark:opacity-100">
    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/50 to-black/30"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-6 py-16 w-full">
        <div class="max-w-3xl">
            <x-typography.h1 class="!text-white text-3xl sm:text-4xl md:!text-5xl lg:!text-6xl drop-shadow-md mb-4 font-normal tracking-wide">Saung Babakan Cinta</x-typography.h1>
            <x-typography.p variant="large" class="!text-white/90 mb-8 max-w-xl font-light text-base md:text-lg lg:text-xl">Sauyunan dina rasa, babarengan dina carita</x-typography.p>
            <div class="flex flex-wrap gap-3">
                <a href="#catering" class="inline-flex items-center justify-center px-6 py-2.5 sm:px-7 sm:py-3 rounded-full bg-white text-black font-semibold text-sm hover:bg-white/90 transition-all duration-300 shadow-md">Pesan Sekarang</a>
                <a href="#menu-dinein" class="inline-flex items-center justify-center px-6 py-2.5 sm:px-7 sm:py-3 rounded-full border border-white/30 text-white font-medium text-sm hover:bg-white/10 transition-all duration-300 backdrop-blur-sm">Lihat Menu</a>
            </div>
        </div>
    </div>
</section>
