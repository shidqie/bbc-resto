@props([
    'id' => null,
    'title' => null,
    'subtitle' => null,
    'bgBatik' => false,
    'bgImage' => null,
])

<section {{ $id ? 'id='.$id : '' }} {{ $attributes->merge(['class' => 'relative py-10 md:py-14 overflow-hidden ' . ($bgBatik && !$bgImage ? 'bg-primary/[0.02] bg-batik' : '')]) }}>
    @if($bgImage)
        <img src="{{ $bgImage }}" alt="Background" class="absolute inset-0 w-full h-full object-cover z-[1]">
        <div class="absolute inset-0 bg-white/70 dark:bg-black/60 z-[2]"></div>
    @endif
    <div class="relative z-10 max-w-[1280px] mx-auto px-6">
        @if($title)
            <x-typography.h2 class="text-center mb-3 {{ $bgImage ? 'text-primary' : '' }}">{{ $title }}</x-typography.h2>
            <div class="w-12 h-[1px] bg-secondary mx-auto {{ $subtitle ? 'mb-4' : 'mb-8' }}"></div>
        @endif
        @if($subtitle)
            <x-typography.p class="text-center mb-10 max-w-2xl mx-auto">{{ $subtitle }}</x-typography.p>
        @endif
        
        {{ $slot }}
    </div>
</section>
