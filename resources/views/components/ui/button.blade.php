{{--
|--------------------------------------------------------------------------
| Button Component
|--------------------------------------------------------------------------
| Komponen tombol serbaguna. Bisa render <button> atau <a> (link).
| Jika prop `href` diisi, maka render <a>. Jika tidak, render <button>.
|
| Props:
|   - variant  (string) : primary, secondary, danger, success, outline, ghost
|   - type     (string) : Tipe tombol HTML: button, submit
|   - href     (string) : URL tujuan (jika diisi, render <a>)
|   - icon     (string) : Nama Heroicon (opsional, contoh: "plus")
|   - size     (string) : xs, sm, md (default: md)
|   - loading  (bool)   : Tampilkan spinner loading
|
| Contoh Pemakaian:
|   <x-ui.button href="/menu/create" icon="plus">Tambah Menu</x-ui.button>
|   <x-ui.button variant="danger" type="submit">Hapus</x-ui.button>
|   <x-ui.button variant="secondary" href="/back">Kembali</x-ui.button>
|   <x-ui.button variant="primary" type="submit" loading>Menyimpan...</x-ui.button>
--}}

@props([
    'variant' => 'primary',
    'type'    => 'button',
    'href'    => null,
    'icon'    => null,
    'size'    => 'md',
    'loading' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-medium transition-all duration-150 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary'   => 'bg-gray-900 text-white hover:bg-gray-800 focus:ring-gray-400 shadow-sm',
        'secondary' => 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 focus:ring-gray-200 shadow-sm',
        'danger'    => 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-300 shadow-sm',
        'success'   => 'bg-emerald-600 text-white hover:bg-emerald-700 focus:ring-emerald-300 shadow-sm',
        'outline'   => 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 focus:ring-gray-200 shadow-sm',
        'ghost'     => 'bg-transparent text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:ring-gray-200',
    ];

    $sizes = [
        'xs' => 'px-2.5 py-1.5 text-xs',   // 32px height — untuk tabel
        'sm' => 'px-3 py-1.5 text-sm',      // 34px height
        'md' => 'px-4 py-2.5 text-sm',      // 40px height — default
    ];

    $iconSizes = [
        'xs' => 'w-3.5 h-3.5',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
    ];

    $variantClass = $variants[$variant] ?? $variants['primary'];
    $sizeClass    = $sizes[$size] ?? $sizes['md'];
    $iconSize     = $iconSizes[$size] ?? $iconSizes['md'];
    $classes      = "$base $variantClass $sizeClass";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        @if($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" :class="$iconSize . ' shrink-0'" />
        @endif
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }} @if($loading) disabled @endif>
        @if($loading)
            <svg class="{{ $iconSize }} shrink-0 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        @elseif($icon)
            <x-dynamic-component :component="'heroicon-o-' . $icon" :class="$iconSize . ' shrink-0'" />
        @endif
        {{ $slot }}
    </button>
@endif
