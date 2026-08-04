@props(['class' => '', 'variant' => 'normal'])

@php
    $baseClass = "font-sans text-gray-600 leading-relaxed";
    
    // Normal: 14px mobile, 16px desktop
    // Small: 12px mobile, 14px desktop
    // Large: 16px mobile, 18px desktop
    $variantClass = match($variant) {
        'small' => 'text-sm md:text-base',
        'large' => 'text-base md:text-lg',
        default => 'text-sm md:text-base',
    };
@endphp

<p {{ $attributes->merge(['class' => "$baseClass $variantClass $class"]) }}>
    {{ $slot }}
</p>
