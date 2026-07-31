{{--
|--------------------------------------------------------------------------
| Button Component
|--------------------------------------------------------------------------
| Komponen tombol serbaguna. Bisa render <button> atau <a> (link).
| Jika prop `href` diisi, maka render <a>. Jika tidak, render <button>.
|
| Props:
|   - variant (string) : Gaya tombol: primary, secondary, danger, outline, ghost
|   - type    (string) : Tipe tombol HTML (hanya untuk <button>): button, submit
|   - href    (string) : URL tujuan (jika diisi, render <a> bukan <button>)
|   - icon    (string) : Kelas icon FontAwesome (opsional, contoh: "fa-plus")
|   - size    (string) : Ukuran: sm, md (default: md)
|
| Contoh Pemakaian:
|   <x-ui.button href="/menu/create" icon="plus">Tambah Menu</x-ui.button>
|   <x-ui.button variant="danger" type="submit">Hapus</x-ui.button>
|   <x-ui.button variant="outline" href="/back">Kembali</x-ui.button>
--}}

@props([
    'variant' => 'primary',
    'type'    => 'button',
    'href'    => null,
    'icon'    => null,
    'size'    => 'md',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-medium transition-all duration-200 rounded-xl focus:outline-none focus:ring-4 disabled:opacity-50 disabled:cursor-not-allowed active:scale-[0.97]';

    $variants = [
        'primary'   => 'bg-primary text-white hover:bg-primary-container focus:ring-primary/30 shadow-sm',
        'secondary' => 'bg-secondary text-white hover:bg-secondary-container focus:ring-secondary/30 shadow-sm',
        'danger'    => 'bg-danger text-white hover:bg-red-700 focus:ring-danger/30 shadow-sm',
        'success'   => 'bg-success text-white hover:bg-green-700 focus:ring-success/30 shadow-sm',
        'outline'   => 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 focus:ring-gray-100 shadow-sm',
        'ghost'     => 'bg-transparent text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:ring-gray-100',
    ];

    $sizes = [
        'sm' => 'px-3 py-1.5 text-xs',
        'md' => 'px-5 py-2.5 text-sm',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass    = $sizes[$size] ?? $sizes['md'];
    $classes      = "$base $variantClass $sizeClass";
@endphp

@if($href)
    {{-- Render sebagai <a> link --}}
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) 
            <x-dynamic-component :component="'heroicon-s-' . $icon" class="w-4 h-4 shrink-0" />
        @endif
        {{ $slot }}
    </a>
@else
    {{-- Render sebagai <button> --}}
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon) 
            <x-dynamic-component :component="'heroicon-s-' . $icon" class="w-4 h-4 shrink-0" />
        @endif
        {{ $slot }}
    </button>
@endif
