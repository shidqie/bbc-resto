<x-landing.section id="galeri" title="Galeri">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        @foreach([
            asset('images/fasad_rumah_kayu.webp'),
            asset('images/pemandangan_malam.webp'),
            asset('images/taman_kafe.webp'),
            asset('images/saungbabakan.webp'),
            asset('images/homepage.webp')
        ] as $img)
            <div class="rounded-2xl overflow-hidden aspect-square">
                <img src="{{ $img }}" alt="Galeri" class="w-full h-full object-cover hover:scale-105 transition-transform duration-300">
            </div>
        @endforeach
    </div>
</x-landing.section>
