{{--
|--------------------------------------------------------------------------
| Action Dropdown Item Component
|--------------------------------------------------------------------------
| Item di dalam <x-ui.action-dropdown>.
|
| Props:
|   - href    (string) : URL jika item berupa link
|   - label   (string) : Teks label menu
|   - icon    (string) : Nama heroicon (misal: pencil-square, trash, printer, power)
|   - variant (string) : neutral, primary, danger, success, warning (default: neutral)
|   - type    (string) : button / submit (default: button)
--}}

@props([
    'href'    => null,
    'label'   => '',
    'icon'    => null,
    'variant' => 'neutral',
    'type'    => 'button',
])

@php
    $itemType = strtolower(trim($label . ' ' . $icon));

    if ($variant === 'neutral') {
        if (str_contains($itemType, 'trash') || str_contains($itemType, 'hapus') || str_contains($itemType, 'batal')) {
            $variantClass = 'text-rose-600 hover:bg-rose-50 hover:text-rose-700';
            $iconColor = 'text-rose-500';
        } elseif (str_contains($itemType, 'pencil') || str_contains($itemType, 'edit') || str_contains($itemType, 'ubah')) {
            $variantClass = 'text-slate-700 hover:bg-amber-50 hover:text-amber-700';
            $iconColor = 'text-amber-500';
        } elseif (str_contains($itemType, 'eye') || str_contains($itemType, 'detail')) {
            $variantClass = 'text-slate-700 hover:bg-blue-50 hover:text-blue-700';
            $iconColor = 'text-blue-500';
        } elseif (str_contains($itemType, 'printer') || str_contains($itemType, 'cetak') || str_contains($itemType, 'bukti')) {
            $variantClass = 'text-slate-700 hover:bg-indigo-50 hover:text-indigo-700';
            $iconColor = 'text-indigo-500';
        } elseif (str_contains($itemType, 'check') || str_contains($itemType, 'verifikasi') || str_contains($itemType, 'aktifkan')) {
            $variantClass = 'text-emerald-700 hover:bg-emerald-50 hover:text-emerald-800';
            $iconColor = 'text-emerald-600';
        } else {
            $variantClass = 'text-gray-700 hover:bg-gray-100 hover:text-gray-900';
            $iconColor = 'text-gray-500';
        }
    } else {
        $variants = [
            'neutral' => 'text-gray-700 hover:bg-gray-100 hover:text-gray-900',
            'primary' => 'text-blue-600 hover:bg-blue-50 hover:text-blue-700',
            'danger'  => 'text-rose-600 hover:bg-rose-50 hover:text-rose-700',
            'success' => 'text-emerald-600 hover:bg-emerald-50 hover:text-emerald-700',
            'warning' => 'text-amber-600 hover:bg-amber-50 hover:text-amber-700',
        ];

        $iconColors = [
            'neutral' => 'text-gray-500',
            'primary' => 'text-blue-500',
            'danger'  => 'text-rose-500',
            'success' => 'text-emerald-500',
            'warning' => 'text-amber-500',
        ];

        $variantClass = $variants[$variant] ?? $variants['neutral'];
        $iconColor = $iconColors[$variant] ?? $iconColors['neutral'];
    }

    $baseClass = "group w-full text-left px-3 py-2 flex items-center gap-2.5 text-xs font-medium transition-all duration-150 cursor-pointer $variantClass";
@endphp

@if($href)
    <a href="{{ $href }}" @click="open = false" {{ $attributes->merge(['class' => $baseClass]) }}>
        @if($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-3.5 h-3.5 shrink-0 {{ $iconColor }} group-hover:scale-110 transition-transform duration-150" />
        @else
            {{ $slot }}
        @endif
        <span>{{ $label }}</span>
    </a>
@else
    <button type="{{ $type }}" @click="open = false" {{ $attributes->merge(['class' => $baseClass]) }}>
        @if($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" class="w-3.5 h-3.5 shrink-0 {{ $iconColor }} group-hover:scale-110 transition-transform duration-150" />
        @else
            {{ $slot }}
        @endif
        <span>{{ $label }}</span>
    </button>
@endif
