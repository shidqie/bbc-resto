{{--
|--------------------------------------------------------------------------
| Stat Card Component
|--------------------------------------------------------------------------
| Komponen untuk menampilkan kartu statistik KPI (Key Performance Indicator).
|
| Props:
|   - label (string) : Label/judul statistik (contoh: "Pesanan Hari Ini")
|   - value (mixed)  : Nilai yang ditampilkan (contoh: 42 atau "Rp 500.000")
|   - icon  (string) : Kelas icon FontAwesome (contoh: "fa-shopping-bag")
|   - color (string) : Tema warna: blue, green, orange, red, gray (default: blue)
|
| Contoh Pemakaian:
|   <x-ui.stat-card label="Pesanan Hari Ini" :value="$count" icon="fa-shopping-bag" color="blue" />
--}}

@props([
    'label' => 'Label',
    'value' => 0,
    'icon'  => 'fa-chart-bar',
    'color' => 'blue',
])

@php
    // Mapping warna untuk icon background dan teks
    $colorMap = [
        'blue'   => ['bg' => 'bg-blue-100',    'text' => 'text-blue-600',    'circle' => 'bg-blue-50'],
        'green'  => ['bg' => 'bg-emerald-100',  'text' => 'text-emerald-600', 'circle' => 'bg-emerald-50'],
        'orange' => ['bg' => 'bg-orange-100',   'text' => 'text-orange-600',  'circle' => 'bg-orange-50'],
        'red'    => ['bg' => 'bg-red-100',      'text' => 'text-red-600',     'circle' => 'bg-red-50'],
        'gray'   => ['bg' => 'bg-gray-100',     'text' => 'text-gray-600',    'circle' => 'bg-gray-50'],
    ];

    $c = $colorMap[$color] ?? $colorMap['blue'];
@endphp

<div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm relative overflow-hidden group">
    {{-- Efek hover dekoratif --}}
    <div class="absolute -right-4 -top-4 w-24 h-24 {{ $c['circle'] }} rounded-full group-hover:scale-150 transition-transform duration-500 z-0"></div>
    
    <div class="relative z-10 flex justify-between items-start">
        <div>
            <div class="text-gray-500 text-xs font-medium mb-1">{{ $label }}</div>
            <div class="text-2xl font-bold {{ $c['text'] }}">{{ $value }}</div>
        </div>
        <div class="w-10 h-10 rounded-xl {{ $c['bg'] }} {{ $c['text'] }} flex items-center justify-center">
            <i class="fas {{ $icon }}"></i>
        </div>
    </div>
</div>
