{{--
|--------------------------------------------------------------------------
| Action Button Component
|--------------------------------------------------------------------------
| Komponen tombol aksi untuk tabel. Support icon + teks opsional.
| Gaya: neutral (default), danger (hapus). Renders <a> jika ada href.
|
| Props:
|   - title   (string) : Tooltip title
|   - href    (string) : URL tujuan
|   - variant (string) : neutral, danger (default: neutral)
|   - label   (string) : Teks di samping ikon (opsional)
|
| Contoh:
|   <x-ui.action-button href="/detail" title="Detail" label="Detail">
|       <x-heroicon-o-eye class="w-4 h-4" />
|   </x-ui.action-button>
|   <x-ui.action-button variant="danger" title="Hapus" @click="hapus()">
|       <x-heroicon-o-trash class="w-4 h-4" />
|   </x-ui.action-button>
--}}

@props([
    'title'   => '',
    'href'    => null,
    'variant' => 'neutral',
    'label'   => '',
])

@php
    $variants = [
        'neutral' => 'text-gray-700 bg-gray-100 hover:bg-gray-200 hover:text-gray-900 border border-gray-200/90 shadow-2xs',
        'primary' => 'text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 shadow-2xs',
        'success' => 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 shadow-2xs',
        'warning' => 'text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 shadow-2xs',
        'danger'  => 'text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 shadow-2xs',
        'ghost'   => 'text-gray-500 hover:text-gray-900 hover:bg-gray-100',
    ];

    $variantClass = $variants[$variant] ?? $variants['neutral'];
    $base = "inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer $variantClass";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['title' => $title, 'class' => $base]) }}>
        {{ $slot }}
        @if($label)<span>{{ $label }}</span>@endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'title' => $title, 'class' => $base]) }}>
        {{ $slot }}
        @if($label)<span>{{ $label }}</span>@endif
    </button>
@endif
