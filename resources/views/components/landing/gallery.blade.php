@props(['galeri' => collect()])

<x-landing.section id="galeri" title="Galeri">
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
        @if(isset($galeri) && $galeri->count() > 0)
            @foreach($galeri as $item)
                <div class="group relative overflow-hidden rounded-2xl aspect-square bg-canvas shadow-sm">
                    <img src="{{ asset('storage/' . $item->foto) }}" alt="Galeri" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-500"></div>
                </div>
            @endforeach
        @else
            @foreach([
                asset('images/fasad_rumah_kayu.webp'),
                asset('images/pemandangan_malam.webp'),
                asset('images/taman_kafe.webp'),
                asset('images/saungbabakan.webp'),
                asset('images/homepage.webp')
            ] as $img)
                <div class="group relative overflow-hidden rounded-2xl aspect-square bg-canvas shadow-sm">
                    <img src="{{ $img }}" alt="Galeri" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors duration-500"></div>
                </div>
            @endforeach
        @endif
    </div>
</x-landing.section>