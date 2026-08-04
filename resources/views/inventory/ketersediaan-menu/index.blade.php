{{-- Halaman: Ketersediaan Menu (FR-12) --}}
@extends('layouts.pos')
@section('title', 'Ketersediaan Menu')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Ketersediaan Menu</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Ketersediaan dihitung per layanan: Dine-In & Nasi Box (Stok Harian) dan Paket Catering (Stok Catering).</p>
            </div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Total Menu Aktif</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Tersedia (Harian)</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $stats['tersedia_harian'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Tidak Cukup (Harian)</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['tidak_cukup_harian'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Tersedia (Catering)</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $stats['tersedia_catering'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-200 px-4 py-3">
                <p class="text-sm font-medium text-gray-500">Tidak Cukup (Catering)</p>
                <p class="text-xl font-bold text-red-600 mt-1">{{ $stats['tidak_cukup_catering'] }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('ketersediaan-menu.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari menu..." />
                <div class="relative shrink-0">
                    <select name="kategori" class="w-full appearance-none rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-9 text-sm text-gray-900 shadow-sm outline-none transition-all focus:border-gray-400 focus:ring-1 focus:ring-gray-400" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
                <div class="relative shrink-0">
                    <select name="jenis_menu" class="w-full appearance-none rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-9 text-sm text-gray-900 shadow-sm outline-none transition-all focus:border-gray-400 focus:ring-1 focus:ring-gray-400" onchange="this.form.submit()">
                        <option value="">Semua Jenis</option>
                        @foreach($jenisMenus as $jenis)
                            <option value="{{ $jenis->id }}" {{ request('jenis_menu') == $jenis->id ? 'selected' : '' }}>{{ $jenis->nama_jenis }}</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                </div>
                <button type="submit" class="text-sm font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->hasAny(['search', 'kategori', 'jenis_menu']))
                    <a href="{{ route('ketersediaan-menu.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Menu</th>
                        <th class="px-4 py-3 text-left">Kategori</th>
                        <th class="px-4 py-3 text-right">Harga</th>
                        <th class="px-4 py-3 text-center">Stok Harian</th>
                        <th class="px-4 py-3 text-center">Stok Catering</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($menus as $i => $menu)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">{{ $menus->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $menu->nama_menu }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $menu->kode_menu }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600 font-medium">{{ $menu->kategori_menu->nama_kategori ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-sm text-gray-700 font-semibold">{{ number_format($menu->harga_jual, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex flex-col items-center gap-1">
                                @if($menu->status_harian === 'Tersedia')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Tersedia
                                    </span>
                                @elseif($menu->status_harian === 'Stok Menipis')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-amber-50 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Menipis
                                    </span>
                                @elseif($menu->status_harian === 'Stok Tidak Cukup')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-red-50 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Tidak Cukup
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-gray-100 text-gray-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>{{ $menu->status_harian }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">±{{ number_format($menu->porsi_harian, 0) }} porsi</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="inline-flex flex-col items-center gap-1">
                                @if($menu->status_catering === 'Tersedia')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-emerald-50 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Tersedia
                                    </span>
                                @elseif($menu->status_catering === 'Stok Menipis')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-amber-50 text-amber-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Menipis
                                    </span>
                                @elseif($menu->status_catering === 'Stok Tidak Cukup')
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-red-50 text-red-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Tidak Cukup
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl bg-gray-100 text-gray-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>{{ $menu->status_catering }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">±{{ number_format($menu->porsi_catering, 0) }} porsi</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="document" title="Belum ada data menu" message="Tidak ada menu aktif yang ditemukan." :colspan="6" />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 shrink-0">{{ $menus->links() }}</div>

    </div>
</div>
@endsection
