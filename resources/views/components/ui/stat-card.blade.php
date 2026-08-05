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
            'iconBg'    => 'bg-[#F5F1E6] text-[#0D3024]',
        ],
        'green' => [
            'iconBg'    => 'bg-[#E8F0EC] text-[#0D3024]',
        ],
        'orange' => [
            'iconBg'    => 'bg-[#F5F1E6] text-[#0D3024]',
        ],
        'red' => [
            'iconBg'    => 'bg-[#FBF0EC] text-[#A43E2A]',
        ],
        'brand' => [
            'iconBg'    => 'bg-[#0D3024] text-[#D4A843]',
        ],
        'violet' => [
            'iconBg'    => 'bg-[#F5F1E6] text-[#0D3024]',
        ],
        'sky' => [
            'iconBg'    => 'bg-[#E8F0EC] text-[#0D3024]',
        ],
        'rose' => [
            'iconBg'    => 'bg-[#FBF0EC] text-[#A43E2A]',
        ],
        'emerald' => [
            'iconBg'    => 'bg-[#E8F0EC] text-[#0D3024]',
        ],
        'purple' => [
            'iconBg'    => 'bg-[#F5F1E6] text-[#0D3024]',
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
