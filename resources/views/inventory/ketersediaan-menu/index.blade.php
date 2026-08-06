{{-- Halaman: Ketersediaan Menu (FR-12) --}}
@extends('layouts.pos')
@section('title', 'Ketersediaan Menu')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Ketersediaan Menu" subtitle="Ketersediaan dihitung per layanan: Dine-In & Nasi Box (Stok Harian) dan Paket Katering (Stok Katering)." :breadcrumbs="['Persediaan', 'Ketersediaan Menu']" />

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Menu Aktif</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Tersedia (Harian)</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['tersedia_harian'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Tidak Cukup (Harian)</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['tidak_cukup_harian'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider mb-1">Tersedia (Katering)</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['tersedia_catering'] }}</span>
            </div>
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-center">
                <span class="text-xs font-semibold text-red-600 uppercase tracking-wider mb-1">Tidak Cukup (Katering)</span>
                <span class="text-2xl font-bold text-gray-900">{{ $stats['tidak_cukup_catering'] }}</span>
            </div>
        </div>

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$menus">
            <x-slot:toolbar>
                <form action="{{ route('ketersediaan-menu.index') }}" method="GET" class="flex items-center gap-2 w-full flex-wrap">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari menu..." />

                    <x-ui.multi-select name="kategori" :options="$kategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori')" label="Kategori" type="radio" />

                    <x-ui.multi-select name="jenis_menu" :options="$jenisMenus->pluck('nama_jenis', 'id')->toArray()" :selected="request('jenis_menu')" label="Jenis" type="radio" />

                    @if(request()->hasAny(['search', 'kategori', 'jenis_menu']))
                        <a href="{{ route('ketersediaan-menu.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[900px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No.</th>
                    <th class="px-4 py-3.5 text-left">Menu</th>
                    <th class="px-4 py-3.5 text-left">Kategori</th>
                    <th class="px-4 py-3.5 text-right">Harga</th>
                    <th class="px-4 py-3.5 text-center">Stok Harian</th>
                    <th class="px-4 py-3.5 text-center">Stok Katering</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($menus as $i => $menu)
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-500 font-medium align-middle">{{ $menus->firstItem() + $i }}</td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $menu->nama_menu }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $menu->kode_menu }}</p>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-600 font-medium">{{ $menu->kategori_menu->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-4 text-right text-sm text-gray-700 font-semibold">{{ number_format($menu->harga_jual, 0, ',', '.') }}</td>
                        <td class="px-4 py-4 text-center">
                            <div class="inline-flex flex-col items-center gap-1">
                                @if($menu->status_harian === 'Tersedia')
                                    <x-ui.badge color="success" size="sm" dot>Tersedia</x-ui.badge>
                                @elseif($menu->status_harian === 'Stok Menipis')
                                    <x-ui.badge color="warning" size="sm" dot>Menipis</x-ui.badge>
                                @elseif($menu->status_harian === 'Stok Tidak Cukup')
                                    <x-ui.badge color="danger" size="sm" dot>Tidak Cukup</x-ui.badge>
                                @else
                                    <x-ui.badge color="gray" size="sm" dot>{{ $menu->status_harian }}</x-ui.badge>
                                @endif
                                <span class="text-xs text-gray-400">±{{ number_format($menu->porsi_harian, 0) }} porsi</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <div class="inline-flex flex-col items-center gap-1">
                                @if($menu->status_catering === 'Tersedia')
                                    <x-ui.badge color="success" size="sm" dot>Tersedia</x-ui.badge>
                                @elseif($menu->status_catering === 'Stok Menipis')
                                    <x-ui.badge color="warning" size="sm" dot>Menipis</x-ui.badge>
                                @elseif($menu->status_catering === 'Stok Tidak Cukup')
                                    <x-ui.badge color="danger" size="sm" dot>Tidak Cukup</x-ui.badge>
                                @else
                                    <x-ui.badge color="gray" size="sm" dot>{{ $menu->status_catering }}</x-ui.badge>
                                @endif
                                <span class="text-xs text-gray-400">±{{ number_format($menu->porsi_catering, 0) }} porsi</span>
                            </div>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="document-text" title="Belum ada data menu" message="Tidak ada menu aktif yang ditemukan." :colspan="6" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

    </div>
</div>
@endsection
