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
            <x-typography.h2 class="text-center mb-3 {{ $bgImage ? 'text-primary' : '' }}">{{ $title }}</x-typography.h2>
        @endif
        @if($subtitle)
            <x-typography.p class="text-center mb-10 max-w-2xl mx-auto">{{ $subtitle }}</x-typography.p>
        @endif
        
        {{ $slot }}
    </div>
</section>
