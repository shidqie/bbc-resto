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
        'neutral' => 'text-gray-500 hover:text-gray-900 hover:bg-gray-100',
        'danger'  => 'text-gray-400 hover:text-red-600 hover:bg-red-50',
    ];

    $variantClass = $variants[$variant] ?? $variants['neutral'];
    $base = "inline-flex items-center justify-center gap-1.5 px-2 py-1.5 rounded-md text-xs font-medium transition-colors $variantClass";
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
