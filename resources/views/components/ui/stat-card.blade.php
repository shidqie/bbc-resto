{{--
|--------------------------------------------------------------------------
| Stat Card Component (Ultra-Minimalist Style)
|--------------------------------------------------------------------------
| Kartu statistik KPI bersih, minimalis, dan elegan.
--}}

@props([
    'label' => 'Label',
    'value' => 0,
    'icon'  => 'shopping-bag',
    'color' => 'blue',
])

@php
    $colorMap = [
        'blue' => [
            'valueText' => 'text-[#2563EB]',
            'iconBg'    => 'bg-blue-50 text-[#2563EB]',
        ],
        'green' => [
            'valueText' => 'text-[#059669]',
            'iconBg'    => 'bg-emerald-50 text-[#059669]',
        ],
        'orange' => [
            'valueText' => 'text-[#D97706]',
            'iconBg'    => 'bg-amber-50 text-[#D97706]',
        ],
        'red' => [
            'valueText' => 'text-[#DC2626]',
            'iconBg'    => 'bg-red-50 text-[#DC2626]',
        ],
        'brand' => [
            'valueText' => 'text-[#0D3024]',
            'iconBg'    => 'bg-emerald-50 text-[#0D3024]',
        ],
        'violet' => [
            'valueText' => 'text-[#7C3AED]',
            'iconBg'    => 'bg-violet-50 text-[#7C3AED]',
        ],
        'sky' => [
            'valueText' => 'text-[#0284C7]',
            'iconBg'    => 'bg-sky-50 text-[#0284C7]',
        ],
        'rose' => [
            'valueText' => 'text-[#E11D48]',
            'iconBg'    => 'bg-rose-50 text-[#E11D48]',
        ],
    ];

    $c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="bg-white rounded-xl p-5 border border-neutral-200/80 shadow-sm transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 flex items-center justify-between">
    <div>
        <p class="text-sm font-semibold text-gray-400 mb-1.5 tracking-wide">{{ $label }}</p>
        <p class="text-3xl font-extrabold {{ $c['valueText'] }} tracking-tight">{{ $value }}</p>
    </div>

    <div class="w-11 h-11 rounded-lg {{ $c['iconBg'] }} flex items-center justify-center text-lg shrink-0">
        <x-heroicon-o-sparkles class="{{ $icon }} w-5 h-5" />
    </div>
</div>
