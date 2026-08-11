<section id="beranda" class="relative flex items-center overflow-hidden" style="min-height: calc(100vh - 80px);">
    <img src="{{ asset('images/homepage.webp') }}" alt="Saung Babakan Cinta" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 max-w-[1280px] mx-auto px-6 py-20 w-full">
        <div class="max-w-3xl">
            <x-typography.h1 class="!text-white md:!text-3xl lg:!text-4xl drop-shadow-lg mb-6">Saung Babakan Cinta</x-typography.h1>
            <x-typography.p variant="large" class="!text-white/90 mb-10 max-w-2xl">Hidangan Sunda otentik dalam suasana saung yang nyaman. Makan di tempat, catering, dan nasi box.</x-typography.p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ route('pesan.catering') }}" onclick="handleOrderClick(event, this.href)" class="bg-primary text-white px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-primary-container transition-all shadow-sm">Pesan Sekarang</a>
                <a href="#menu-dinein" class="bg-surface text-primary border-2 border-primary/10 px-5 py-2.5 rounded-xl font-bold text-sm hover:bg-surface-hover transition-all">Lihat Menu</a>
            </div>
        </div>
    </div>
</section>
