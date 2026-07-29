{{-- 
    Halaman: Manajemen Menu
    Fitur: FR-MN-01 s/d FR-MN-06 (Tambah, Edit, Nonaktifkan Menu; Kelola Kategori)
    Desain: Minimalis — satu halaman, dua tab (Menu & Kategori)
--}}
@extends('layouts.pos')

@section('content')
<style>
    /* View Mode Styles */
    .view-mode input:disabled, 
    .view-mode select:disabled, 
    .view-mode textarea:disabled {
        background-color: transparent !important;
        border-color: transparent !important;
        color: #111827 !important;
        font-weight: 500;
        padding-left: 0 !important;
        padding-right: 0 !important;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: none !important;
        resize: none;
        box-shadow: none !important;
    }
    .view-mode .border-t {
        border-color: transparent !important;
    }
    .view-mode select:disabled {
        /* Fix for select text truncation if any */
        text-overflow: unset;
    }
</style>
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Manajemen Menu</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola menu berdasarkan layanan dan kategorinya.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openKategoriModal()" id="btnAddKategori" class="hidden inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Kategori Baru
                </button>
                <button onclick="openMenuModal()" id="btnAddMenu" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Menu Baru
                </button>
            </div>
        </div>

        <x-ui.alert />

        @if(session('warning_bom'))
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800">
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-800 shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <span>{{ session('warning_bom') }}</span>
        </div>
        @endif

        {{-- TABS --}}
        <div class="flex border-b border-gray-200 gap-1 shrink-0">
            <button id="tabMenu" onclick="switchTab('menu')" class="px-4 py-2.5 text-sm font-semibold text-gray-900 border-b-2 border-gray-900 -mb-px transition-colors">
                Menu <span class="ml-1.5 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ $stats['total'] }}</span>
            </button>
            <button id="tabKategori" onclick="switchTab('kategori')" class="px-4 py-2.5 text-sm font-medium text-gray-500 border-b-2 border-transparent -mb-px hover:text-gray-700 transition-colors">
                Kategori <span class="ml-1.5 text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2 py-0.5">{{ $kategoris->total() }}</span>
            </button>
        </div>

        {{-- ============ TAB: DAFTAR MENU ============ --}}
        <div id="paneMenu">
            {{-- Filter bar --}}
            <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
                <form action="{{ route('menu.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-none sm:w-56">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode…" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                        <input type="hidden" name="jenis_menu" value="{{ request('jenis_menu') }}">
                    </div>
                    <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                </form>
                <div class="flex items-center gap-1 text-xs font-medium overflow-x-auto no-scrollbar shrink-0">
                    <span class="text-gray-500 mr-1">Filter Layanan:</span>
                    <a href="{{ route('menu.index') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ !request('jenis_menu') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                    <a href="{{ route('menu.index', ['jenis_menu' => 'dine_in']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis_menu') === 'dine_in' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Resto ({{ $stats['dine_in'] }})</a>
                    <a href="{{ route('menu.index', ['jenis_menu' => 'catering']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis_menu') === 'catering' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Catering ({{ $stats['catering'] }})</a>
                    <a href="{{ route('menu.index', ['jenis_menu' => 'nasi_box']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis_menu') === 'nasi_box' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Nasi Box ({{ $stats['nasi_box'] }})</a>
                </div>
            </div>

            {{-- Menu Table --}}
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left w-12">No.</th>
                            <th class="px-4 py-3 text-left">Menu</th>
                            <th class="px-4 py-3 text-left">Layanan</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-left">Harga</th>
                            <th class="px-4 py-3 text-left">BOM</th>
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($menus as $menu)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    @if($menu->foto)
                                        <img src="{{ Storage::url($menu->foto) }}" class="w-9 h-9 rounded-lg object-cover border border-gray-100 shrink-0" alt="">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-semibold text-gray-900 leading-tight">{{ $menu->nama }}</p>
                                        <p class="text-xs text-gray-400 font-mono">{{ $menu->kode_menu ?? 'MNU-'.str_pad($menu->id,2,'0',STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $jenisColor = ['catering' => 'bg-blue-50 text-blue-700', 'nasi_box' => 'bg-purple-50 text-purple-700', 'dine_in' => 'bg-emerald-50 text-emerald-700'];
                                    $jenisLabel = ['catering' => 'Catering', 'nasi_box' => 'Nasi Box', 'dine_in' => 'Resto'];
                                    $jColor = $jenisColor[$menu->jenis_menu] ?? 'bg-gray-100 text-gray-600';
                                    $jLabel = $jenisLabel[$menu->jenis_menu] ?? $menu->jenis_menu;
                                @endphp
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md {{ $jColor }}">{{ $jLabel }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-700 font-medium">{{ $menu->kategori->nama ?? '—' }}</span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-900 tabular-nums">
                                Rp {{ number_format($menu->harga, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3">
                                @if($menu->resep->isNotEmpty())
                                    <span class="inline-block text-xs text-emerald-700 bg-emerald-50 border border-emerald-100 rounded-md px-2 py-0.5 font-medium">{{ $menu->resep->count() }} bahan</span>
                                @else
                                    <span class="inline-block text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-md px-2 py-0.5 font-medium">Tanpa BOM</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($menu->status === 'nonaktif')
                                    <span class="inline-block text-xs font-medium text-gray-500 bg-gray-100 rounded-full px-2.5 py-0.5">Nonaktif</span>
                                @elseif($menu->isHabis())
                                    <span class="inline-block text-xs font-medium text-red-600 bg-red-50 rounded-full px-2.5 py-0.5">Stok Habis</span>
                                @else
                                    <span class="inline-block text-xs font-medium text-emerald-700 bg-emerald-50 rounded-full px-2.5 py-0.5">Aktif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                    {{-- View (Read) --}}
                                    <button onclick="openMenuModal({{ json_encode($menu) }}, {{ json_encode($menu->resep) }}, true)" class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors" title="Lihat">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    {{-- Edit (Update) --}}
                                    <button onclick="openMenuModal({{ json_encode($menu) }}, {{ json_encode($menu->resep) }})" class="p-1.5 rounded-md text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    {{-- Delete (Hapus) --}}
                                    <form action="{{ route('menu.destroy', $menu->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus menu {{ addslashes($menu->nama) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-14 text-gray-400">
                                <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                <p class="text-sm font-medium">Belum ada menu</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4 shrink-0">{{ $menus->links() }}</div>
        </div>

        {{-- ============ TAB: KATEGORI MENU ============ --}}
        <div id="paneKategori" class="hidden">
            {{-- Filter --}}
            <div class="flex items-center gap-1 text-xs font-medium mb-3 shrink-0">
                <a href="{{ route('menu.index', ['tab' => 'kategori']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ !request('jenis') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                <a href="{{ route('menu.index', ['tab' => 'kategori', 'jenis' => 'dine_in']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis') === 'dine_in' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Resto</a>
                <a href="{{ route('menu.index', ['tab' => 'kategori', 'jenis' => 'catering']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis') === 'catering' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Catering</a>
                <a href="{{ route('menu.index', ['tab' => 'kategori', 'jenis' => 'nasi_box']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis') === 'nasi_box' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Nasi Box</a>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left w-12">No.</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-left">Layanan</th>
                            <th class="px-4 py-3 text-left">Jumlah Menu</th>
                            <th class="px-4 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($kategoris as $kat)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900">{{ $kat->nama }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $kat->kode_kategori ?? 'KTG-'.str_pad($kat->id,2,'0',STR_PAD_LEFT) }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $kJenisColor = ['catering' => 'bg-blue-50 text-blue-700', 'nasi_box' => 'bg-purple-50 text-purple-700', 'dine_in' => 'bg-emerald-50 text-emerald-700'];
                                    $kJenisLabel = ['catering' => 'Catering', 'nasi_box' => 'Nasi Box', 'dine_in' => 'Resto'];
                                @endphp
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md {{ $kJenisColor[$kat->jenis_menu] ?? 'bg-gray-100 text-gray-600' }}">{{ $kJenisLabel[$kat->jenis_menu] ?? $kat->jenis_menu }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 font-medium">
                                {{ $kat->menus_count ?? 0 }} menu
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                    <button onclick="openKategoriModal({{ $kat->id }}, '{{ addslashes($kat->nama) }}', '{{ $kat->jenis_menu ?? 'dine_in' }}')" class="p-1.5 rounded-md text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <form action="{{ route('kategori-menu.destroy', $kat->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus kategori {{ addslashes($kat->nama) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-14 text-gray-400">
                                <p class="text-sm font-medium">Belum ada kategori</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $kategoris->links() }}</div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: TAMBAH/EDIT MENU (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerMenu" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeMenuModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerMenuPanel">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div>
                <h2 class="font-semibold text-gray-900" id="menuModalTitle">Tambah Menu Baru</h2>
                <p class="text-xs text-gray-400 mt-0.5" id="menuModalSubtitle">Isi informasi menu dan komposisi bahan baku</p>
            </div>
            <button onclick="closeMenuModal()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Body --}}
        <form id="formMenu" action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto">
            @csrf
            <div id="formMenuMethod"></div>
            <div class="px-5 py-5 space-y-4">

                {{-- Nama --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Menu <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" id="mnNama" required placeholder="Contoh: Ayam Bakar Madu" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>

                {{-- Layanan + Kategori --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Layanan <span class="text-red-500">*</span></label>
                        <select name="jenis_menu" id="mnJenis" required onchange="filterKat(this.value)" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="dine_in">Resto</option>
                            <option value="catering">Catering</option>
                            <option value="nasi_box">Nasi Box</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori_menu_id" id="mnKategori" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="">— Pilih Kategori —</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" data-jenis="{{ $kat->jenis_menu }}">{{ $kat->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Harga + Status --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="harga" id="mnHarga" required min="0" placeholder="25000" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="mnStatus" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="tersedia">Aktif (Tersedia)</option>
                            <option value="habis">Stok Habis</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" id="mnDeskripsi" rows="2" placeholder="Penjelasan singkat mengenai menu…" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all resize-none"></textarea>
                </div>

                {{-- Foto --}}
                <div id="mnFotoContainer">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Foto Menu (Opsional)</label>
                    <input type="file" id="mnFoto" name="foto" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                </div>

                {{-- BOM Section --}}
                <div class="pt-2 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Komposisi Bahan Baku (BOM)</p>
                            <p class="text-xs text-gray-400">Bahan untuk 1 porsi/paket</p>
                        </div>
                        <button type="button" id="btnTambahBom" onclick="addBomRow()" class="text-xs font-medium text-gray-700 border border-gray-200 rounded-lg px-2.5 py-1.5 hover:bg-gray-50 transition-colors flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah
                        </button>
                    </div>
                    <div id="bomRows" class="space-y-2">
                        {{-- JS rendered rows --}}
                    </div>
                    <p id="bomEmpty" class="text-xs text-gray-400 text-center py-3">Belum ada bahan baku. Menu bisa tetap disimpan tanpa BOM.</p>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0">
                <button type="button" id="btnBatalMenu" onclick="closeMenuModal()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" id="btnSimpanMenu" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-5 py-2 hover:bg-gray-800 transition-colors">Simpan Menu</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: TAMBAH/EDIT KATEGORI              --}}
{{-- ══════════════════════════════════════════ --}}
<div id="modalKategori" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeKategoriModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-gray-900" id="katModalTitle">Tambah Kategori</h3>
            <button onclick="closeKategoriModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="formKategori" action="{{ route('kategori-menu.store') }}" method="POST" class="space-y-4">
            @csrf
            <div id="formKatMethod"></div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="katNama" required placeholder="Contoh: Makanan Utama" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">Layanan <span class="text-red-500">*</span></label>
                <select name="jenis_menu" id="katJenis" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                    <option value="dine_in">Resto</option>
                    <option value="catering">Catering</option>
                    <option value="nasi_box">Nasi Box</option>
                </select>
            </div>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" onclick="closeKategoriModal()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-5 py-2 hover:bg-gray-800 transition-colors">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
const bahanBakusData = @json($bahanBakus);
const BASE_URL = '{{ url('/') }}';

// ═══ TABS ═══
function switchTab(tab) {
    const paneMenu = document.getElementById('paneMenu');
    const paneKat = document.getElementById('paneKategori');
    const tabMenu = document.getElementById('tabMenu');
    const tabKat = document.getElementById('tabKategori');
    const btnAdd = document.getElementById('btnAddMenu');
    const btnKat = document.getElementById('btnAddKategori');

    if (tab === 'menu') {
        paneMenu.classList.remove('hidden');
        paneKat.classList.add('hidden');
        tabMenu.classList.add('text-gray-900', 'border-gray-900');
        tabMenu.classList.remove('text-gray-500', 'border-transparent');
        tabKat.classList.remove('text-gray-900', 'border-gray-900');
        tabKat.classList.add('text-gray-500', 'border-transparent');
        btnAdd.classList.remove('hidden');
        btnAdd.style.display = '';
        btnKat.style.display = 'none';
    } else {
        paneKat.classList.remove('hidden');
        paneMenu.classList.add('hidden');
        tabKat.classList.add('text-gray-900', 'border-gray-900');
        tabKat.classList.remove('text-gray-500', 'border-transparent');
        tabMenu.classList.remove('text-gray-900', 'border-gray-900');
        tabMenu.classList.add('text-gray-500', 'border-transparent');
        btnAdd.style.display = 'none';
        btnKat.classList.remove('hidden');
        btnKat.style.display = '';
    }
}

// Init tab from URL param
(function() {
    const url = new URLSearchParams(window.location.search);
    if (url.get('tab') === 'kategori') switchTab('kategori');
    else switchTab('menu');
})();

// ═══ MENU DRAWER ═══
function openMenuModal(menu = null, reseps = null, isView = false) {
    isViewMode = isView;
    const drawer = document.getElementById('drawerMenu');
    const panel = document.getElementById('drawerMenuPanel');
    
    document.getElementById('formMenu').action = '{{ route("menu.store") }}';
    document.getElementById('formMenuMethod').innerHTML = '';
    
    if (isView) {
        document.getElementById('menuModalTitle').textContent = 'Detail Menu';
    } else {
        document.getElementById('menuModalTitle').textContent = menu ? 'Edit Menu' : 'Tambah Menu Baru';
    }
    document.getElementById('menuModalSubtitle').textContent = menu ? (menu.kode_menu ?? '') : 'Isi informasi menu dan komposisi bahan baku';

    document.getElementById('mnNama').value = menu?.nama ?? '';
    document.getElementById('mnJenis').value = menu?.jenis_menu ?? 'dine_in';
    filterKat(menu?.jenis_menu ?? 'dine_in');
    
    document.getElementById('mnKategori').value = menu?.kategori_menu_id ?? '';
    document.getElementById('mnHarga').value = menu?.harga ? Math.round(menu.harga) : '';
    document.getElementById('mnStatus').value = menu?.status ?? 'tersedia';
    document.getElementById('mnDeskripsi').value = menu?.deskripsi ?? '';

    // Handle View Mode specific UI
    const inputs = ['mnNama', 'mnJenis', 'mnKategori', 'mnHarga', 'mnStatus', 'mnDeskripsi'];
    inputs.forEach(id => {
        document.getElementById(id).disabled = isView;
    });

    if (isView) {
        document.getElementById('formMenu').classList.add('view-mode');
        document.getElementById('mnFotoContainer').classList.add('hidden');
        document.getElementById('btnTambahBom').classList.add('hidden');
        document.getElementById('btnSimpanMenu').classList.add('hidden');
        document.getElementById('btnBatalMenu').textContent = 'Tutup';
    } else {
        document.getElementById('formMenu').classList.remove('view-mode');
        document.getElementById('mnFotoContainer').classList.remove('hidden');
        document.getElementById('btnTambahBom').classList.remove('hidden');
        document.getElementById('btnSimpanMenu').classList.remove('hidden');
        document.getElementById('btnBatalMenu').textContent = 'Batal';
    }

    if (menu) {
        document.getElementById('formMenu').action = `${BASE_URL}/menu/${menu.id}`;
        document.getElementById('formMenuMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    }

    // BOM rows
    document.getElementById('bomRows').innerHTML = '';
    if (reseps && reseps.length > 0) {
        reseps.forEach(r => addBomRow(r.bahan_baku_id, r.jumlah_kebutuhan));
    }
    updateBomEmpty();

    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    requestAnimationFrame(() => {
        panel.classList.remove('translate-x-full');
    });
}

function closeMenuModal() {
    const drawer = document.getElementById('drawerMenu');
    const panel = document.getElementById('drawerMenuPanel');
    
    // Animate out
    panel.classList.add('translate-x-full');
    setTimeout(() => {
        drawer.classList.add('hidden');
        drawer.style.display = '';
    }, 300);
}

// ═══ BOM ROWS ═══
function addBomRow(selId = '', qty = '') {
    const container = document.getElementById('bomRows');
    const row = document.createElement('div');
    row.className = 'flex items-center gap-2 bom-row';

    let opts = '<option value="">— Pilih Bahan —</option>';
    bahanBakusData.forEach(b => {
        const satuanNama = (b.satuan && b.satuan.nama_satuan) ? b.satuan.nama_satuan : '';
        opts += `<option value="${b.id}" data-satuan="${satuanNama}" ${b.id == selId ? 'selected' : ''}>${b.nama_bahan}</option>`;
    });

    row.innerHTML = `
        <select name="bahan_baku_id[]" onchange="updateSatuanLabel(this)" ${isViewMode ? 'disabled' : ''} class="flex-1 px-3 py-2 text-xs border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400">${opts}</select>
        <input type="number" name="jumlah_kebutuhan[]" step="0.01" min="0.01" value="${qty}" ${isViewMode ? 'disabled' : ''} placeholder="Qty" class="w-20 px-3 py-2 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 font-medium text-center">
        <span class="bom-satuan text-xs text-gray-400 w-10 text-center shrink-0">-</span>
        ${!isViewMode ? `<button type="button" onclick="this.closest('.bom-row').remove(); updateBomEmpty();" class="p-1.5 text-gray-400 hover:text-red-500 transition-colors shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>` : ''}
    `;
    container.appendChild(row);

    const sel = row.querySelector('select');
    if (selId) updateSatuanLabel(sel);
    updateBomEmpty();
}

function updateSatuanLabel(sel) {
    const opt = sel.options[sel.selectedIndex];
    const satuan = opt?.getAttribute('data-satuan') ?? '-';
    sel.closest('.bom-row').querySelector('.bom-satuan').textContent = satuan || '-';
}

function updateBomEmpty() {
    const rows = document.getElementById('bomRows').querySelectorAll('.bom-row');
    const empty = document.getElementById('bomEmpty');
    if (empty) empty.style.display = rows.length > 0 ? 'none' : '';
}

function filterKat(jenis) {
    const sel = document.getElementById('mnKategori');
    const opts = sel.querySelectorAll('option');
    opts.forEach(o => {
        if (!o.value) return;
        o.style.display = (!jenis || o.getAttribute('data-jenis') === jenis) ? '' : 'none';
    });
    if (sel.options[sel.selectedIndex]?.style.display === 'none') sel.value = '';
}

// ═══ KATEGORI MODAL ═══
function openKategoriModal(id = null, nama = '', jenis = 'dine_in') {
    document.getElementById('katModalTitle').textContent = id ? 'Edit Kategori' : 'Tambah Kategori';
    document.getElementById('formKategori').action = id ? `${BASE_URL}/kategori-menu/${id}` : '{{ route("kategori-menu.store") }}';
    document.getElementById('formKatMethod').innerHTML = id ? '<input type="hidden" name="_method" value="PUT">' : '';
    document.getElementById('katNama').value = nama;
    document.getElementById('katJenis').value = jenis;

    document.getElementById('modalKategori').classList.remove('hidden');
    document.getElementById('modalKategori').style.display = 'flex';
}

function closeKategoriModal() {
    document.getElementById('modalKategori').classList.add('hidden');
    document.getElementById('modalKategori').style.display = '';
}
</script>
@endsection
