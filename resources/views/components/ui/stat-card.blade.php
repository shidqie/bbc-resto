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
    ];

    $c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="bg-white rounded-[2.25rem] p-5 border border-gray-200/90 shadow-md transition-all duration-200 hover:shadow-lg hover:-translate-y-0.5 flex items-center justify-between">
    <div>
        <p class="text-xs font-semibold text-gray-400 mb-1 tracking-wide">{{ $label }}</p>
        <p class="text-2xl font-extrabold {{ $c['valueText'] }} tracking-tight">{{ $value }}</p>
    </div>

    <div class="w-11 h-11 rounded-full {{ $c['iconBg'] }} flex items-center justify-center text-lg shrink-0">
        <x-heroicon-o-sparkles class="{{ $icon }} w-5 h-5" />
    </div>
</div>
