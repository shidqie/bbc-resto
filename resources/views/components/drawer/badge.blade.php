@props(['variant' => 'neutral'])

@php
    $colors = match($variant) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/50',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200/50',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200/50',
        'info' => 'bg-primary-soft text-primary border-primary/20',
        default => 'bg-gray-100 text-gray-700 border-gray-200/50', // neutral
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border $colors"]) }}>
    {{ $slot }}
</span>
