{{--
|--------------------------------------------------------------------------
| Stat Card Component (Ultra-Minimalist Style)
|--------------------------------------------------------------------------
| Kartu statistik KPI bersih, minimalis, dan elegan bertema emas-hijau.
| Flat tanpa shadow, ikon seragam, value monokrom dengan aksen brand.
|--}}

@props([
    'label' => 'Label',
    'value' => 0,
    'icon'  => 'shopping-bag',
    'color' => 'brand',
    'hint'  => '',
])

@php
    $colorMap = [
        'blue' => [
            'iconBg'    => 'bg-secondary-soft text-primary',
        ],
        'green' => [
            'iconBg'    => 'bg-primary-soft text-primary',
        ],
        'orange' => [
            'iconBg'    => 'bg-secondary-soft text-primary',
        ],
        'red' => [
            'iconBg'    => 'bg-red-50 text-red-700',
        ],
        'brand' => [
            'iconBg'    => 'bg-primary text-accent dark:text-neutral-900',
        ],
        'violet' => [
            'iconBg'    => 'bg-secondary-soft text-primary',
        ],
        'sky' => [
            'iconBg'    => 'bg-primary-soft text-primary',
        ],
        'rose' => [
            'iconBg'    => 'bg-red-50 text-red-700',
        ],
        'emerald' => [
            'iconBg'    => 'bg-primary-soft text-primary',
        ],
        'purple' => [
            'iconBg'    => 'bg-secondary-soft text-primary',
        ],
    ];

    $c = $colorMap[$color] ?? $colorMap['brand'];
@endphp

<div class="bg-white rounded-xl p-5 border border-neutral-200/80 flex items-center justify-between">
    <div class="min-w-0">
        <p class="text-xs font-medium text-gray-400 mb-1.5 tracking-wide uppercase">{{ $label }}</p>
        <p class="text-2xl font-bold text-gray-900 tracking-tight truncate">{{ $value }}</p>
        @if($hint)
            <p class="text-xs text-gray-400 font-medium mt-1">{{ $hint }}</p>
        @endif
    </div>

    <div class="w-10 h-10 rounded-lg {{ $c['iconBg'] }} flex items-center justify-center shrink-0">
        <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-5 h-5" />
    </div>
</div>
