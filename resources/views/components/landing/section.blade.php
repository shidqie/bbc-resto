@props([
    'id' => null,
    'title' => null,
    'subtitle' => null,
    'bgBatik' => false,
    'bgImage' => null,
])

<section {{ $id ? 'id='.$id : '' }} {{ $attributes->merge(['class' => 'relative py-16 md:py-24 overflow-hidden ' . ($bgBatik && !$bgImage ? 'bg-primary/[0.02] bg-batik' : '')]) }}>
    @if($bgImage)
        <img src="{{ $bgImage }}" alt="Background" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-canvas/30"></div>
    @endif
    <div class="relative z-10 max-w-[1280px] mx-auto px-6">
        @if($title)
            <h2 class="text-2xl md:text-3xl text-center mb-3 {{ $bgImage ? 'text-primary' : '' }}">{{ $title }}</h2>
        @endif
        @if($subtitle)
            <p class="text-body text-center text-sm mb-10 max-w-2xl mx-auto">{{ $subtitle }}</p>
        @endif
        
        {{ $slot }}
    </div>
</section>
