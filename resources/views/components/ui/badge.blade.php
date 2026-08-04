{{--
|--------------------------------------------------------------------------
| Badge Component
|--------------------------------------------------------------------------
| Komponen badge/label status. Bisa dengan atau tanpa dot indicator.
|
| Props:
|   - color   (string) : Tema warna: primary, success, warning, danger, gray
|   - dot     (bool)   : Tampilkan dot indicator di depan teks (default: false)
|   - size    (string) : Ukuran badge: sm, md (default: md)
|
| Contoh Pemakaian:
|   <x-ui.badge color="success" dot>Aman</x-ui.badge>
|   <x-ui.badge color="danger">Habis</x-ui.badge>
--}}

@props([
    'color' => 'gray',
    'dot'   => false,
    'size'  => 'md',
])

@php
    // Mapping warna badge
    $colors = [
        'primary' => 'bg-blue-50 text-blue-700 border-blue-200/50',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/50',
        'warning' => 'bg-orange-50 text-orange-700 border-orange-200/50',
        'danger'  => 'bg-red-50 text-red-700 border-red-200/50',
        'gray'    => 'bg-gray-100 text-gray-700 border-gray-200',
    ];

    // Mapping warna dot
    $dots = [
        'primary' => 'bg-blue-500',
        'success' => 'bg-emerald-500',
        'warning' => 'bg-orange-500',
        'danger'  => 'bg-red-500',
        'gray'    => 'bg-gray-500',
    ];

    // Ukuran
    $sizes = [
        'sm' => 'text-xs px-2 py-0.5',
        'md' => 'text-sm px-2.5 py-1',
    ];

    $colorClass = $colors[$color] ?? $colors['gray'];
    $dotClass   = $dots[$color] ?? $dots['gray'];
    $sizeClass  = $sizes[$size] ?? $sizes['md'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-medium rounded-full border $colorClass $sizeClass"]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
    @endif
    {{ $slot }}
</span>
