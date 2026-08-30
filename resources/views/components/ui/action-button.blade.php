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
    $actionType = strtolower(trim($label ?: $title));

    // Palet warna hover spesifik sesuai konteks aksi
    if ($variant === 'neutral') {
        if (str_contains($actionType, 'detail') || str_contains($actionType, 'lihat')) {
            $variantClass = 'text-slate-700 bg-white hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 border border-gray-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
        } elseif (str_contains($actionType, 'edit') || str_contains($actionType, 'ubah') || str_contains($actionType, 'atur')) {
            $variantClass = 'text-slate-700 bg-white hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 border border-gray-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
        } elseif (str_contains($actionType, 'hapus') || str_contains($actionType, 'delete')) {
            $variantClass = 'text-rose-600 bg-white hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 border border-gray-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
        } elseif (str_contains($actionType, 'bukti') || str_contains($actionType, 'struk') || str_contains($actionType, 'cetak') || str_contains($actionType, 'pdf') || str_contains($actionType, 'print')) {
            $variantClass = 'text-slate-700 bg-white hover:bg-indigo-50 hover:text-indigo-700 hover:border-indigo-200 border border-gray-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
        } elseif (str_contains($actionType, 'verifikasi') || str_contains($actionType, 'terima') || str_contains($actionType, 'selesai')) {
            $variantClass = 'text-emerald-700 bg-emerald-50/70 hover:bg-emerald-100 hover:text-emerald-800 hover:border-emerald-300 border border-emerald-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
        } elseif (str_contains($actionType, 'batal') || str_contains($actionType, 'gagal')) {
            $variantClass = 'text-rose-700 bg-rose-50/70 hover:bg-rose-100 hover:text-rose-800 hover:border-rose-300 border border-rose-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
        } elseif (str_contains($actionType, 'mulai') || str_contains($actionType, 'kirim')) {
            $variantClass = 'text-blue-700 bg-blue-50/70 hover:bg-blue-100 hover:text-blue-800 hover:border-blue-300 border border-blue-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
        } else {
            $variantClass = 'text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 border border-gray-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
        }
    } else {
        $variants = [
            'primary' => 'text-blue-700 bg-blue-50/70 hover:bg-blue-100 hover:text-blue-800 border border-blue-200 hover:border-blue-300 hover:shadow-xs active:scale-[0.98]',
            'success' => 'text-emerald-700 bg-emerald-50/70 hover:bg-emerald-100 hover:text-emerald-800 border border-emerald-200 hover:border-emerald-300 hover:shadow-xs active:scale-[0.98]',
            'warning' => 'text-amber-700 bg-amber-50/70 hover:bg-amber-100 hover:text-amber-800 border border-amber-200 hover:border-amber-300 hover:shadow-xs active:scale-[0.98]',
            'danger'  => 'text-rose-700 bg-rose-50/70 hover:bg-rose-100 hover:text-rose-800 border border-rose-200 hover:border-rose-300 hover:shadow-xs active:scale-[0.98]',
            'ghost'   => 'text-gray-500 hover:text-gray-900 hover:bg-gray-100 active:scale-[0.98]',
        ];
        $variantClass = $variants[$variant] ?? 'text-slate-700 bg-white hover:bg-slate-50 hover:text-slate-900 hover:border-slate-300 border border-gray-200 shadow-2xs hover:shadow-xs active:scale-[0.98]';
    }

    $base = "group inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-150 cursor-pointer $variantClass";
@endphp

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['title' => $title, 'class' => $base]) }}>
        <span class="shrink-0 transition-transform duration-150 group-hover:scale-110 flex items-center justify-center">
            {{ $slot }}
        </span>
        @if($label)<span>{{ $label }}</span>@endif
    </a>
@else
    <button {{ $attributes->merge(['type' => 'button', 'title' => $title, 'class' => $base]) }}>
        <span class="shrink-0 transition-transform duration-150 group-hover:scale-110 flex items-center justify-center">
            {{ $slot }}
        </span>
        @if($label)<span>{{ $label }}</span>@endif
    </button>
@endif
