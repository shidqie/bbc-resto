<x-landing.section id="galeri" title="Galeri">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-primary/10">
        @foreach([
            asset('images/fasad_rumah_kayu.webp'),
            asset('images/pemandangan_malam.webp'),
            asset('images/taman_kafe.webp'),
            asset('images/saungbabakan.webp'),
            asset('images/homepage.webp')
        ] as $img)
            <div class="overflow-hidden aspect-square bg-canvas">
                <img src="{{ $img }}" alt="Galeri" class="w-full h-full object-cover opacity-90 hover:opacity-100 transition-opacity duration-500">
            </div>
        @endforeach
    </div>
</x-landing.section>