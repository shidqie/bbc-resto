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
    .view-mode label span.text-red-500 {
        display: none;
    }
</style>
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Manajemen Menu & Paket" subtitle="Kelola menu berdasarkan layanan dan kategorinya." :breadcrumbs="['Manajemen Menu', 'Data Menu']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <x-ui.button variant="secondary" icon="plus" onclick="openKategoriModal()" id="btnAddKategori" class="hidden">
                        Kategori Baru
                    </x-ui.button>
                    
                    <div class="relative" x-data="{ open: false }">
                        <x-ui.button variant="primary" icon="plus" @click="open = !open" id="btnAddMenu">
                            Tambah Menu
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 ml-1 transition-transform" :class="open ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </x-ui.button>

                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            <button @click="open = false; openMenuModal('dinein')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary transition-colors flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" /></svg>
                                Menu Dine-In
                            </button>
                            <button @click="open = false; openMenuModal('paket')" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary transition-colors flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                Paket
                            </button>
                        </div>
                    </div>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- TABS --}}
        <x-ui.tab-list>
            <x-ui.tab href="{{ route('menu.index', ['jenis_menu_id' => 'all']) }}" :active="$jenisId === 'all'">
                Semua Menu
            </x-ui.tab>
            <x-ui.tab href="{{ route('menu.index', ['jenis_menu_id' => 1]) }}" :active="$jenisId == 1">
                Menu Dine In
            </x-ui.tab>
            <x-ui.tab href="{{ route('menu.index', ['jenis_menu_id' => 3]) }}" :active="$jenisId == 3">
                Paket Nasi Box
            </x-ui.tab>
            <x-ui.tab href="{{ route('menu.index', ['jenis_menu_id' => 2]) }}" :active="$jenisId == 2">
                Paket Katering
            </x-ui.tab>
        </x-ui.tab-list>

        {{-- Menu Table --}}
        <x-ui.data-table :paginator="$menus">
            <x-slot:toolbar>
                <form action="{{ route('menu.index') }}" method="GET" class="w-full flex flex-wrap items-center gap-3">
                    <input type="hidden" name="jenis_menu_id" value="{{ request('jenis_menu_id', '1') }}">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode…" width="w-full sm:w-72" />
                    <x-ui.multi-select name="kategori_id" :options="$allKategoris->pluck('nama_kategori', 'id')->toArray()" :selected="request('kategori_id')" label="Kategori" type="radio" />
                    <x-ui.multi-select name="status" :options="['tersedia' => 'Tersedia', 'habis' => 'Habis', 'nonaktif' => 'Nonaktif']" :selected="request('status')" label="Status Ketersediaan" type="radio" />
                    <x-ui.multi-select name="filter_resep" :options="['ada' => 'Sudah Ada Resep', 'belum' => 'Belum Ada Resep']" :selected="request('filter_resep')" label="Resep Menu" type="radio" />
                    @if(request()->hasAny(['search', 'kategori_id', 'status', 'filter_resep']))
                        <x-ui.button href="{{ route('menu.index', ['jenis_menu_id' => request('jenis_menu_id', '1')]) }}" variant="danger" size="sm">Reset</x-ui.button>
                    @endif
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[820px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Foto</th>
                    <th class="px-4 py-3.5 text-left">Nama Menu</th>
                    <th class="px-4 py-3.5 text-left">Kategori</th>
                    <th class="px-4 py-3.5 text-left">Harga</th>
                    <th class="px-4 py-3.5 text-center">Status Ketersediaan</th>
                    @if($jenisId != 1 && $jenisId !== 'all')
                        <th class="px-4 py-3.5 text-left">Komponen</th>
                    @endif
                    <th class="px-4 py-3.5 text-center">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                        @forelse($menus as $menu)
                        <x-ui.table.row>
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                                {{ $menus->firstItem() + $loop->index }}
                            </td>
                            <td class="px-4 py-3">
                                @if($menu->foto)
                                    <img src="{{ Storage::url($menu->foto) }}" alt="{{ $menu->nama_menu }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                {{ $menu->nama_menu }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $menu->kategori_menu->nama_kategori ?? '–' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                Rp{{ number_format($menu->harga_jual, 0, ',', '.') }}
                                @if($menu->jenis_menu_id == '2' || $menu->jenis_menu_id == 'catering')
                                    /porsi
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center align-middle">
                                @if(!$menu->status_aktif)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                                        Nonaktif
                                    </span>
                                @elseif(!empty($menu->is_habis))
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                        Habis
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Tersedia
                                    </span>
                                @endif
                            </td>
                            @if($jenisId != 1 && $jenisId !== 'all')
                                <td class="px-4 py-3 text-sm text-gray-700">
                                    @if($menu->komponen_paket && $menu->komponen_paket->count() > 0)
                                        <span class="font-semibold text-gray-900">{{ $menu->komponen_paket->count() }}</span>
                                        <span class="text-gray-500">komponen</span>
                                    @else
                                        <span class="text-gray-400">Belum ada</span>
                                    @endif
                                </td>
                            @endif
                            <td class="px-4 py-3 text-center text-sm font-medium">
                                <div class="flex items-center justify-center gap-2">
                                    <x-ui.action-button onclick="openMenuModal({{ $menu->id }}, true)" title="Detail">
                                        <x-heroicon-o-eye class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <x-ui.action-button onclick="openMenuModal({{ $menu->id }}, false)" title="Ubah">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <x-ui.action-button onclick="deleteMenu({{ $menu->id }})" title="Hapus">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </x-ui.action-button>
                                </div>
                            </td>
                        </x-ui.table.row>
                        @empty
                        <tr>
                            <td colspan="{{ $jenisId != 1 && $jenisId !== 'all' ? 8 : 7 }}">
                                <x-ui.empty-state icon="archive-box" title="Belum ada menu" message="Tambahkan menu untuk mulai melayani pelanggan." />
                            </td>
                        </tr>
                        @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>


    </div>
</div>

{{-- Modal Detail Resep (Read-Only) --}}
<div id="resepDetailModalOverlay" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-[60] hidden opacity-0 transition-opacity duration-300" onclick="closeResepDetailModal()"></div>
<div id="resepDetailModalPanel" class="fixed inset-y-0 right-0 w-full md:w-[600px] bg-white shadow-2xl z-[70] transform translate-x-full transition-transform duration-300 flex flex-col hidden">
    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-white shrink-0">
        <div>
            <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">Detail Resep Menu</h2>
        </div>
        <button onclick="closeResepDetailModal()" class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>

    {{-- Body --}}
    <div class="flex-1 overflow-y-auto bg-gray-50/30 p-6 space-y-6">
        
        {{-- Info Menu --}}
        <div class="bg-white border border-gray-100 rounded-xl p-5 shadow-sm space-y-3">
            <div class="grid grid-cols-3 gap-2 text-sm">
                <div class="text-gray-500">Kode Menu</div>
                <div class="col-span-2 font-medium text-gray-900 flex items-center gap-2">
                    <span class="text-gray-400">:</span> <span id="rdmKodeMenu">-</span>
                </div>
                
                <div class="text-gray-500">Nama Menu</div>
                <div class="col-span-2 font-medium text-gray-900 flex items-center gap-2">
                    <span class="text-gray-400">:</span> <span id="rdmNamaMenu">-</span>
                </div>
                
                <div class="text-gray-500">Kategori</div>
                <div class="col-span-2 font-medium text-gray-900 flex items-center gap-2">
                    <span class="text-gray-400">:</span> <span id="rdmKategori">-</span>
                </div>
                
                <div class="text-gray-500">Porsi Resep</div>
                <div class="col-span-2 font-medium text-gray-900 flex items-center gap-2">
                    <span class="text-gray-400">:</span> <span>1 porsi</span>
                </div>
            </div>
        </div>

        {{-- Tabel Bahan Baku --}}
        <div class="bg-white border border-gray-100 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-900 text-sm">BAHAN BAKU</h3>
            </div>
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-5 py-3 font-semibold w-16">No</th>
                        <th class="px-5 py-3 font-semibold">Bahan Baku</th>
                        <th class="px-5 py-3 font-semibold text-right">Jumlah</th>
                        <th class="px-5 py-3 font-semibold">Satuan</th>
                    </tr>
                </thead>
                <tbody id="rdmTableBody" class="divide-y divide-gray-100">
                    <!-- Rows injected via JS -->
                </tbody>
            </table>
            <div id="rdmEmptyState" class="hidden py-8 text-center text-gray-400 text-sm">
                Tidak ada bahan baku / resep belum lengkap.
            </div>
        </div>

    </div>

    {{-- Footer --}}
    <div class="p-4 border-t border-gray-100 bg-white shrink-0 flex justify-end">
        <button type="button" onclick="closeResepDetailModal()" class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            Tutup
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: TAMBAH/EDIT MENU (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerMenu" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="drawerMenuOverlay" onclick="closeMenuModal()"></div>
    <div class="absolute right-0 top-0 h-full w-[calc(100vw-280px)] bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerMenuPanel">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 pt-4 pb-2 border-b border-gray-100 shrink-0">
            <div>
                <h2 class="font-semibold text-gray-900" id="menuModalTitle">Tambah Menu Baru</h2>
                <p class="text-xs text-gray-400 mt-0.5" id="menuModalSubtitle">Isi informasi menu dan komposisi bahan baku</p>
            </div>
            <button onclick="closeMenuModal()" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Tabs Header --}}
        <div class="flex px-5 border-b border-gray-100 shrink-0">
            <button type="button" onclick="switchMenuTab('informasi')" id="tabBtnInformasi" class="py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-gray-900 text-gray-900">Informasi Menu</button>
            <button type="button" onclick="switchMenuTab('resep')" id="tabBtnResep" class="py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 ml-4">Resep Bahan Baku</button>
            <button type="button" onclick="switchMenuTab('daftar_menu_paket')" id="tabBtnDaftarMenuPaket" class="hidden py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 ml-4">Resep Menu Paket</button>
        </div>

        {{-- Form Body --}}
        <form id="formMenu" action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" novalidate class="flex-1 overflow-y-auto flex flex-col justify-between">
            @csrf
            <div id="formMenuMethod"></div>
            
            @if ($errors->any())
            <div class="px-5 mt-4">
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg text-sm">
                    <strong class="font-bold">Gagal menyimpan!</strong>
                    <ul class="list-disc pl-5 mt-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            
            {{-- Tab: Informasi --}}
            <div id="tabContentInformasi" class="px-5 py-5">
                
                {{-- Mode Edit / Create --}}
                <div id="infoEditMode" class="space-y-4">
                    {{-- Nama --}}
                    <div>
                        <x-ui.textarea name="nama" id="mnNama" label="Nama Menu *" rows="2" required placeholder="Contoh: Ayam Bakar Madu" />
                    </div>



                    {{-- Komponen Paket Section (Hanya untuk Catering & Nasi Box) --}}
                    <div id="komponenPaketContainerEdit" class="hidden border border-gray-100 bg-gray-50/50 p-4 rounded-xl space-y-4">
                        @include('admin.menu.paket.partials.komponen-builder', ['existingKomponen' => [], 'menus' => $masterMenus ?? []])
                    </div>

                    {{-- Layanan + Kategori --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Layanan <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="jenis_menu_id" id="mnJenis" required onchange="filterKat(this.value)" class="w-full appearance-none px-3.5 py-2.5 pr-9 text-sm font-semibold border border-gray-200 rounded-xl bg-white outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-xs cursor-pointer">
                                    <option value="1">Dine in</option>
                                    <option value="2">Katering</option>
                                    <option value="3">Nasi Box</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <x-heroicon-o-chevron-down class="w-4 h-4" />
                                </span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="kategori_menu_id" id="mnKategori" required class="w-full appearance-none px-3.5 py-2.5 pr-9 text-sm font-semibold border border-gray-200 rounded-xl bg-white outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-xs cursor-pointer">
                                    <option value="">— Pilih Kategori —</option>
                                    @foreach($kategoriModalList ?? $allKategoris as $kat)
                                        <option value="{{ $kat->id }}" data-jenis="{{ $kat->jenis_menu_id ?? '' }}">{{ $kat->nama_kategori }}</option>
                                    @endforeach
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <x-heroicon-o-chevron-down class="w-4 h-4" />
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Harga + Status --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <x-ui.input type="number" name="harga" id="mnHarga" label="Harga (Rp) *" required min="0" placeholder="25000" class="font-medium" />
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select name="status" id="mnStatus" required class="w-full appearance-none px-3.5 py-2.5 pr-9 text-sm font-semibold border border-gray-200 rounded-xl bg-white outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-all shadow-xs cursor-pointer">
                                    <option value="tersedia">Aktif (Tersedia)</option>
                                    <option value="habis">Stok Habis</option>
                                    <option value="nonaktif">Nonaktif</option>
                                </select>
                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <x-heroicon-o-chevron-down class="w-4 h-4" />
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <x-ui.textarea name="deskripsi" id="mnDeskripsi" label="Deskripsi" rows="2" placeholder="Penjelasan singkat mengenai menu…" />
                    </div>

                    {{-- Foto --}}
                    <div id="mnFotoContainer">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Foto Menu (Opsional)</label>
                        <input type="file" id="mnFoto" name="foto" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                    </div>
                </div>

                {{-- Mode View --}}
                <div id="infoViewMode" class="hidden grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="border border-gray-200 rounded-xl bg-white p-5 space-y-5">
                        <div>
                            <div class="text-xs text-gray-500 mb-1.5">Nama Menu</div>
                            <div class="text-xl font-bold text-gray-900" id="viewNamaMenu">-</div>
                        </div>

                        <div id="komponenPaketContainerView" class="hidden border-t border-gray-100 pt-4 space-y-2">
                            <div class="text-xs font-bold text-gray-500 uppercase tracking-wider">Item Menu</div>
                            <div id="viewKomponenList"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <div class="text-xs text-gray-500 mb-1.5">Layanan</div>
                                <div class="font-medium text-gray-900" id="viewLayanan">-</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1.5">Kategori</div>
                                <div class="font-medium text-gray-900" id="viewKategori">-</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <div class="text-xs text-gray-500 mb-1.5">Harga</div>
                                <div class="font-medium text-gray-900" id="viewHarga">-</div>
                            </div>
                            <div>
                                <div class="text-xs text-gray-500 mb-1.5">Status</div>
                                <div id="viewStatus">-</div>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <div class="text-xs text-gray-500 mb-1.5">Deskripsi</div>
                            <div class="text-sm text-gray-700" id="viewDeskripsi">-</div>
                        </div>
                    </div>
                    <div class="border border-gray-200 rounded-xl bg-white p-5">
                        <div class="font-semibold text-gray-900 mb-3">Foto Menu</div>
                        <div class="rounded-xl overflow-hidden bg-gray-50 aspect-square w-full max-w-sm mx-auto flex items-center justify-center border border-gray-100">
                            <img id="viewFotoMenu" src="" class="w-full h-full object-cover hidden" alt="Foto">
                            <span id="viewFotoPlaceholder" class="text-gray-400 text-sm">Tidak ada foto</span>
                        </div>
                    </div>
                </div>

            </div>
            
            {{-- Tab: Resep --}}
            <div id="tabContentResep" class="px-5 py-5 space-y-4 hidden">
                <div id="resepAlertNewMenu" class="hidden bg-amber-50 text-amber-800 p-4 rounded-lg text-sm border border-amber-200">
                    <div class="font-semibold mb-1">Menu Belum Disimpan</div>
                    Silakan isi <strong>Informasi Menu</strong> terlebih dahulu dan klik <strong>Simpan</strong>. Resep hanya dapat ditambahkan setelah menu berhasil dibuat.
                </div>
                
                <div id="resepInputArea" class="bg-gray-50 p-4 rounded-lg border border-gray-100 space-y-3">
                    <div id="resepMenuContextInfo" class="hidden mb-3 pb-3 border-b border-gray-200">
                        <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold">Resep Untuk Menu:</p>
                        <p class="text-sm font-bold text-gray-900" id="resepMenuContextName">-</p>
                    </div>
                    <p class="text-xs text-gray-500">Masukkan bahan baku yang dibutuhkan untuk 1 porsi menu ini.</p>
                    <div class="flex gap-2.5 items-center" x-data="{
                        open: false,
                        search: '',
                        selectedId: '',
                        selectedNama: '',
                        selectedKode: '',
                        selectedSatuanId: '',
                        selectedSatuanText: '',
                        items: bahanBakusData,
                        get filtered() {
                            if (!this.search) return this.items;
                            let q = this.search.toLowerCase();
                            return this.items.filter(i => 
                                (i.nama_bahan && i.nama_bahan.toLowerCase().includes(q)) || 
                                (i.id_bahan_baku && i.id_bahan_baku.toLowerCase().includes(q))
                            );
                        },
                        select(item) {
                            this.selectedId = item.id;
                            this.selectedNama = item.nama_bahan;
                            this.selectedKode = item.id_bahan_baku || '';
                            this.selectedSatuanId = item.satuan_id || '';
                            this.selectedSatuanText = item.satuan ? (item.satuan.singkatan || item.satuan.nama_satuan) : 'pcs';
                            this.search = item.nama_bahan;
                            this.open = false;
                            
                            document.getElementById('inputBahanBaku').value = item.id;
                            document.getElementById('inputSatuan').value = item.satuan_id || '';
                            document.getElementById('inputSatuanTextDisplay').innerText = this.selectedSatuanText;
                        },
                        resetSelection() {
                            this.selectedId = '';
                            this.selectedNama = '';
                            this.selectedKode = '';
                            this.selectedSatuanId = '';
                            this.selectedSatuanText = '';
                            this.search = '';
                            document.getElementById('inputBahanBaku').value = '';
                            document.getElementById('inputSatuan').value = '';
                            document.getElementById('inputJumlah').value = '';
                            document.getElementById('inputSatuanTextDisplay').innerText = '-';
                        }
                    }" @click.outside="open = false" @reset-resep-input.window="resetSelection()" @edit-resep-input.window="
                        let bId = $event.detail.bahanId;
                        let item = items.find(i => i.id == bId);
                        if (item) select(item);
                        document.getElementById('inputJumlah').value = $event.detail.jumlah;
                    ">
                        
                        {{-- Searchable Custom Dropdown --}}
                        <div class="relative flex-1">
                            <svg class="w-4 h-4 text-gray-400 absolute left-3.5 top-3 pointer-events-none z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>

                            <input type="text"
                                   x-model="search"
                                   @focus="open = true"
                                   @click="open = true"
                                   @input="open = true"
                                   placeholder="Cari & pilih bahan baku..."
                                   class="w-full h-10 rounded-xl border border-gray-200 bg-white text-xs pl-10 pr-9 py-2 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all font-medium shadow-2xs text-gray-800">
                            
                            <input type="hidden" id="inputBahanBaku" value="">

                            <button type="button" @click="open = !open" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            {{-- Floating Dropdown Card --}}
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute left-0 z-50 mt-1.5 w-full min-w-[280px] bg-white border border-gray-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-50"
                                 style="display: none;">
                                <template x-for="item in filtered" :key="item.id">
                                    <div @click="select(item)" class="px-4 py-2.5 hover:bg-emerald-50/70 cursor-pointer flex items-center justify-between transition-colors">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-800" x-text="item.nama_bahan"></p>
                                            <p class="text-[10px] text-gray-400 font-medium" x-text="item.id_bahan_baku || ''"></p>
                                        </div>
                                        <span class="text-[10px] px-2.5 py-0.5 bg-gray-100 text-gray-600 rounded-full font-medium" x-text="item.satuan ? (item.satuan.singkatan || item.satuan.nama_satuan) : 'pcs'"></span>
                                    </div>
                                </template>
                                <div x-show="filtered.length === 0" class="px-4 py-3 text-xs text-gray-400 text-center">
                                    Bahan baku tidak ditemukan
                                </div>
                            </div>
                        </div>

                        {{-- Jumlah Input & Satuan Display --}}
                        <div class="w-48 h-10 flex items-center border border-gray-200 rounded-xl bg-white shadow-2xs focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 transition-all overflow-hidden px-3">
                            <input type="number" min="0" step="any" id="inputJumlah" placeholder="Jumlah" 
                                   oninput="if(this.value < 0) this.value = ''" 
                                   class="w-24 py-2 text-xs font-semibold text-gray-900 outline-none bg-transparent placeholder-gray-400 border-none focus:outline-none focus:ring-0">
                            <input type="hidden" id="inputSatuan" value="">
                            <span id="inputSatuanTextDisplay" class="text-[10px] font-bold text-gray-500 uppercase tracking-wider pl-2 border-l border-gray-200 truncate">
                                -
                            </span>
                        </div>

                        {{-- Tambah Button --}}
                        <div>
                            <button type="button" onclick="handleAddResepCustom()" class="h-10 px-4 text-xs font-bold bg-gray-900 text-white rounded-xl hover:bg-gray-800 active:scale-98 transition-all shadow-2xs flex items-center gap-1">
                                + Tambah
                            </button>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Daftar Resep</h4>
                    <div class="border border-gray-100 rounded-lg overflow-hidden bg-white">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-100">
                                <tr>
                                    <th class="px-5 py-3 font-semibold">Bahan Baku</th>
                                    <th class="px-5 py-3 font-semibold text-right w-32">Jumlah</th>
                                    <th class="px-5 py-3 font-semibold w-32">Satuan</th>
                                    <th id="resepAksiHeader" class="px-5 py-3 font-semibold text-center w-32">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="resepContainer" class="divide-y divide-gray-100">
                                <!-- Resep rows will be populated here -->
                            </tbody>
                        </table>
                        <div id="resepEmptyState" class="text-center py-6 text-gray-400 text-sm">
                            Belum ada bahan baku.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab: Resep Menu Paket --}}
            <div id="tabContentDaftarMenuPaket" class="px-5 py-5 space-y-4 hidden">
                
                {{-- Mode Read-Only (Tree / Hierarchy Resep Menu Paket) --}}
                <div id="paketResepViewOnlyMode" class="hidden space-y-4">
                    <div id="paketTreeViewContainer">
                        <!-- Injected via JS -->
                    </div>
                </div>

                {{-- Mode Edit (Tabel Aksi Edit/Tambah Resep) --}}
                <div id="paketResepEditModeTable">
                    <h4 class="text-sm font-semibold text-gray-900 mb-1">Daftar Resep Menu Paket</h4>
                    <p class="text-xs text-gray-500 mb-3">Daftar menu yang termasuk dalam paket ini beserta status resepnya.</p>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Menu</th>
                                    <th class="px-4 py-3 text-center">Status Resep</th>
                                    <th class="px-4 py-3 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100 text-sm text-gray-700" id="daftarMenuPaketContainer">
                            </tbody>
                        </table>
                    </div>
                    <div id="daftarMenuPaketEmpty" class="hidden text-center py-6 text-gray-400 text-sm">Belum ada menu di paket ini.</div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <x-ui.button type="button" variant="secondary" id="btnBatalMenu" onclick="closeMenuModal()">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary" id="btnSimpanMenu">Simpan Menu</x-ui.button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- DRAWER: TAMBAH/EDIT KATEGORI (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerKategori" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeKategoriModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerKategoriPanel">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <div>
                <h3 class="font-semibold text-gray-900" id="katModalTitle">Tambah Kategori</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kelola kategori menu resto, catering, atau nasi box</p>
            </div>
            <button onclick="closeKategoriModal()" class="p-1 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Body --}}
        <form id="formKategori" action="{{ route('kategori-menu.store') }}" method="POST" class="flex-1 overflow-y-auto flex flex-col justify-between">
            @csrf
            <div id="formKatMethod"></div>
            <div class="px-5 py-5 space-y-4">
                <div>
                    <x-ui.input name="nama_kategori" id="katNama" label="Nama Kategori *" required placeholder="Contoh: Makanan Utama" />
                </div>
                <div>
                    <x-ui.textarea name="deskripsi" id="katDeskripsi" label="Deskripsi" placeholder="Tuliskan deskripsi kategori..." rows="3" />
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <x-ui.button type="button" variant="secondary" onclick="closeKategoriModal()">Batal</x-ui.button>
                <x-ui.button type="submit" variant="primary">Simpan Kategori</x-ui.button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: KONFIRMASI HAPUS                  --}}
{{-- ══════════════════════════════════════════ --}}
<div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm mx-4 p-6 space-y-4 text-center">
        <div class="w-14 h-14 rounded-full border-2 border-orange-300 flex items-center justify-center mx-auto">
            <span class="text-orange-400 text-2xl font-bold">!</span>
        </div>
        <div>
            <h3 class="font-semibold text-gray-900 text-base">Konfirmasi</h3>
            <p class="text-sm text-gray-500 font-medium mt-1">Yakin ingin menghapus data ini?</p>
        </div>
        <form id="formHapus" method="POST" action="">
            @csrf @method('DELETE')
            <div class="flex gap-3 justify-center pt-1 w-full">
                <x-ui.button type="button" variant="secondary" class="flex-1 w-full" onclick="closeDeleteModal()">
                    Batal
                </x-ui.button>
                <x-ui.button type="submit" variant="danger" class="flex-1 w-full">
                    Ya, Hapus
                </x-ui.button>
            </div>
        </form>
    </div>
</div>

<script>
const menusData = @json($menus->items());
const BASE_URL = '{{ url('/') }}';

function deleteMenu(id) {
    document.getElementById('formHapus').action = `${BASE_URL}/menu/${id}`;
    document.getElementById('modalHapus').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('modalHapus').classList.add('hidden');
    document.getElementById('formHapus').action = '';
}

function deleteResep(menuId) {
    window.confirmDialog({
        title: 'Hapus Resep Menu',
        name: 'Hapus seluruh bahan baku pada resep ini?',
        message: 'Semua takaran bahan baku yang tersimpan pada menu ini akan dihapus.',
        confirmText: 'Hapus Resep',
        cancelText: 'Batal',
        type: 'danger',
        onConfirm: function () {
            fetch(`${BASE_URL}/menu/${menuId}/resep`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            }).then(response => response.json())
              .then(data => {
                  if (data.success) {
                      window.location.reload();
                  } else if (typeof Swal !== 'undefined') {
                      Swal.fire('Gagal', data.message || 'Gagal menghapus resep', 'error');
                  } else {
                      alert(data.message || 'Gagal menghapus resep');
                  }
              })
              .catch(error => {
                  console.error('Error:', error);
                  if (typeof Swal !== 'undefined') {
                      Swal.fire('Error', 'Terjadi kesalahan sistem saat menghubungi server.', 'error');
                  } else {
                      alert('Terjadi kesalahan sistem saat menghubungi server.');
                  }
              });
        }
    });
}

function createMenuForOption(opsiId, namaPilihan) {
    window.confirmDialog({
        title: 'Buat Menu Otomatis',
        name: `Menu "${namaPilihan}"`,
        message: 'Menu ini belum ada di daftar menu sistem. Buat otomatis sekarang agar bisa ditambahkan resepnya?',
        confirmText: 'Buat Menu',
        cancelText: 'Batal',
        type: 'warning',
        onConfirm: function () {
            fetch(`${BASE_URL}/menu/create-from-option`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ opsi_id: opsiId, nama_menu: namaPilihan })
            }).then(res => res.json()).then(data => {
                if (data.success) {
                    window.location.reload();
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire('Gagal', data.message || 'Gagal membuat menu otomatis.', 'error');
                } else {
                    alert(data.message || 'Gagal membuat menu otomatis.');
                }
            }).catch(err => {
                console.error(err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
                } else {
                    alert('Terjadi kesalahan sistem.');
                }
            });
        }
    });
}

// ═══ MENU DRAWER ═══
function openMenuModal(menuId = null, isView = false, defaultTab = 'informasi') {
    let menu = null;
    let defaultJenis = 1;

    if (menuId === 'dinein') {
        defaultJenis = 1;
        menuId = null;
    } else if (menuId === 'paket') {
        defaultJenis = 3; // Default to Nasi Box for Paket
        menuId = null;
    }

    if (menuId) {
        menu = menusData.find(m => m.id == menuId);
        if (!menu) {
            menu = allMenusData.find(m => m.id == menuId);
        }
    }

    const drawer = document.getElementById('drawerMenu');
    const panel = document.getElementById('drawerMenuPanel');
    const overlay = document.getElementById('drawerMenuOverlay');
    
    document.getElementById('formMenu').action = '{{ route("menu.store") }}';
    document.getElementById('formMenuMethod').innerHTML = '';
    
    if (isView) {
        document.getElementById('menuModalTitle').textContent = 'Detail Menu';
    } else {
        document.getElementById('menuModalTitle').textContent = menu ? 'Edit Menu' : 'Tambah Menu Baru';
    }
    const menuKode = menu?.id_menu ?? '';
    const menuNama = menu?.nama_menu ?? menu?.nama ?? '';
    document.getElementById('menuModalSubtitle').textContent = menu ? (menuKode + (menuNama ? ' - ' + menuNama : '')) : 'Isi informasi menu';

    const jenisVal = menu ? (menu.jenis_menu_id ?? 1) : defaultJenis;
    document.getElementById('mnNama').value = menu ? (menu.nama_menu ?? menu.nama ?? '') : '';
    document.getElementById('mnJenis').value = jenisVal;
    
    filterKat(jenisVal);
    
    document.getElementById('mnKategori').value = menu?.kategori_menu_id ?? '';
    document.getElementById('mnHarga').value = menu ? Math.round(menu.harga_jual ?? menu.harga ?? 0) : '';
    document.getElementById('mnStatus').value = menu ? (menu.status_aktif == false ? 'nonaktif' : 'tersedia') : 'tersedia';
    document.getElementById('mnDeskripsi').value = menu?.deskripsi ?? '';

    // Handle View Mode specific UI
    const inputs = ['mnNama', 'mnJenis', 'mnKategori', 'mnHarga', 'mnStatus', 'mnDeskripsi'];
    inputs.forEach(id => {
        document.getElementById(id).disabled = isView;
    });

    if (isView) {
        document.getElementById('formMenu').classList.add('view-mode');
        document.getElementById('btnSimpanMenu').classList.add('hidden');
        document.getElementById('btnBatalMenu').textContent = 'Tutup';
        
        document.getElementById('infoEditMode').classList.add('hidden');
        document.getElementById('infoViewMode').classList.remove('hidden');
        
        document.getElementById('viewNamaMenu').textContent = menu ? (menu.nama_menu ?? menu.nama) : '-';
        document.getElementById('viewLayanan').textContent = document.getElementById('mnJenis').options[document.getElementById('mnJenis').selectedIndex].text;
        
        const katSelect = document.getElementById('mnKategori');
        document.getElementById('viewKategori').textContent = katSelect.selectedIndex >= 0 ? katSelect.options[katSelect.selectedIndex].text : '-';
        
        document.getElementById('viewHarga').textContent = 'Rp' + parseInt(document.getElementById('mnHarga').value || 0).toLocaleString('id-ID');
        
        const statusVal = document.getElementById('mnStatus').value;
        const statusEl = document.getElementById('viewStatus');
        if (statusVal === 'tersedia') {
            statusEl.innerHTML = '<span class="px-3 py-1 rounded bg-green-50 text-green-700 text-xs font-semibold border border-green-200">Aktif / Tersedia</span>';
        } else if (statusVal === 'habis') {
            statusEl.innerHTML = '<span class="px-3 py-1 rounded bg-red-50 text-red-700 text-xs font-semibold border border-red-200">Stok Habis</span>';
        } else {
            statusEl.innerHTML = '<span class="px-3 py-1 rounded bg-gray-100 text-gray-700 text-xs font-semibold border border-gray-200">Nonaktif</span>';
        }
        
        document.getElementById('viewDeskripsi').textContent = document.getElementById('mnDeskripsi').value || 'Tidak ada deskripsi.';
        
        const imgEl = document.getElementById('viewFotoMenu');
        const placeholderEl = document.getElementById('viewFotoPlaceholder');
        if (menu && menu.foto) {
            imgEl.src = '{{ asset("storage") }}/' + menu.foto;
            imgEl.classList.remove('hidden');
            placeholderEl.classList.add('hidden');
        } else {
            imgEl.classList.add('hidden');
            placeholderEl.classList.remove('hidden');
        }

    } else {
        document.getElementById('formMenu').classList.remove('view-mode');
        document.getElementById('btnSimpanMenu').classList.remove('hidden');
        document.getElementById('btnBatalMenu').textContent = 'Batal';
        
        document.getElementById('infoEditMode').classList.remove('hidden');
        document.getElementById('infoViewMode').classList.add('hidden');
    }

    if (menu && !isView) {
        document.getElementById('formMenu').action = `${BASE_URL}/menu/${menu.id}`;
        document.getElementById('formMenuMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    }

    // Populate resep
    const resepContainer = document.getElementById('resepContainer');
    resepContainer.innerHTML = '';
    
    if (menu && menu.resep_menu && menu.resep_menu.length > 0) {
        menu.resep_menu.forEach(r => {
            const satStr = r.satuan ? (r.satuan.singkatan || r.satuan.nama_satuan) : '';
            addResepRow(r.bahan_baku_id, r.jumlah_kebutuhan || r.jumlah, r.satuan_id, satStr, isView);
        });
    }
    
    checkResepEmptyState();
    
    const resepAlert = document.getElementById('resepAlertNewMenu');
    const resepInputArea = document.getElementById('resepInputArea');
    const resepAksiHeader = document.getElementById('resepAksiHeader');
    const resepContext = document.getElementById('resepMenuContextInfo');
    
    if (isView) {
        if (resepAlert) resepAlert.classList.add('hidden');
        if (resepInputArea) resepInputArea.classList.add('hidden');
        if (resepAksiHeader) resepAksiHeader.style.display = 'none';
        if (resepContext) resepContext.classList.add('hidden');
    } else {
        if (!menu) {
            // New Menu
            if (resepAlert) resepAlert.classList.remove('hidden');
            if (resepInputArea) resepInputArea.classList.add('hidden');
            if (resepAksiHeader) resepAksiHeader.style.display = 'none';
            if (resepContext) resepContext.classList.add('hidden');
        } else {
            // Edit Menu
            if (resepAlert) resepAlert.classList.add('hidden');
            if (resepInputArea) resepInputArea.classList.remove('hidden');
            if (resepAksiHeader) resepAksiHeader.style.display = '';
            if (resepContext) {
                resepContext.classList.remove('hidden');
                document.getElementById('resepMenuContextName').textContent = (menuKode ? menuKode + ' - ' : '') + menuNama;
            }
        }
    }

    let isPaket = false;
    if (menu) {
        // Menu yang sudah ada: dianggap paket HANYA jika memiliki komponen paket ATAU masuk kategori Paket Catering (16) / Paket Nasi Box (17)
        isPaket = (menu.komponen_paket && menu.komponen_paket.length > 0) || [16, 17].includes(Number(menu.kategori_menu_id));
    } else {
        // Menu baru: paket jika dibuat melalui opsi tambah paket
        isPaket = isCreatingPaket;
    }
    const btnResep = document.getElementById('tabBtnResep');
    const btnDaftarMenuPaket = document.getElementById('tabBtnDaftarMenuPaket');
    const komponenContainerEdit = document.getElementById('komponenPaketContainerEdit');
    const komponenContainerView = document.getElementById('komponenPaketContainerView');

    if (isPaket) {
        const btnInformasi = document.getElementById('tabBtnInformasi');
        btnResep.classList.add('hidden');
        btnDaftarMenuPaket.classList.remove('hidden');
        btnInformasi.textContent = 'Informasi Paket';
        btnDaftarMenuPaket.textContent = 'Resep Menu';
        if (komponenContainerEdit) komponenContainerEdit.classList.remove('hidden');
        if (komponenContainerView) komponenContainerView.classList.remove('hidden');

        const viewModeContainer = document.getElementById('paketResepViewOnlyMode');
        const editModeContainer = document.getElementById('paketResepEditModeTable');

        if (isView) {
            if (viewModeContainer) viewModeContainer.classList.remove('hidden');
            if (editModeContainer) editModeContainer.classList.add('hidden');
            renderPaketResepTree(menu);
        } else {
            if (viewModeContainer) viewModeContainer.classList.add('hidden');
            if (editModeContainer) editModeContainer.classList.remove('hidden');

            // Populate Daftar Menu Paket (Edit Mode Table)
            const daftarContainer = document.getElementById('daftarMenuPaketContainer');
            const daftarEmpty = document.getElementById('daftarMenuPaketEmpty');
            daftarContainer.innerHTML = '';
            
            if (menu && menu.komponen_paket && menu.komponen_paket.length > 0) {
            daftarEmpty.classList.add('hidden');
            let no = 1;
            menu.komponen_paket.forEach(komp => {
                const isTetap = komp.tipe_item === 'tetap' || komp.tipe_item === 'wajib';
                
                if (isTetap) {
                    let targetMenuId = null;
                    let namaMenu = komp.nama_item;
                    let kategori = '-';
                    
                    if (komp.menu_terkait) {
                        namaMenu = komp.menu_terkait.nama_menu || komp.nama_item;
                        kategori = komp.menu_terkait.kategori_menu ? komp.menu_terkait.kategori_menu.nama_kategori : '-';
                        targetMenuId = komp.menu_terkait.id;
                    } else {
                        const found = allMenusData.find(m => m.nama_menu === komp.nama_item || m.nama === komp.nama_item);
                        if (found) targetMenuId = found.id;
                    }

                    let statusResep = 'Belum Lengkap';
                    if (targetMenuId) {
                        const found = allMenusData.find(m => m.id === targetMenuId);
                        if (found && found.resep_menu && found.resep_menu.length > 0) {
                            statusResep = 'Lengkap';
                        }
                    }
                    const statusClass = statusResep === 'Lengkap' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';

                    let aksiHtml = '-';
                    if (targetMenuId) {
                        if (statusResep === 'Lengkap') {
                            aksiHtml = `
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button" onclick="openMenuModal(${targetMenuId}, false, 'resep')" class="text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                        Edit Resep
                                    </button>
                                    <button type="button" onclick="deleteResep(${targetMenuId})" class="text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        Hapus Resep
                                    </button>
                                </div>
                            `;
                        } else {
                            aksiHtml = `
                                <button type="button" onclick="openMenuModal(${targetMenuId}, false, 'resep')" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                                    Tambahkan Resep
                                </button>
                            `;
                        }
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-4 py-3 text-gray-500">${no++}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">${namaMenu} <span class="text-xs text-gray-400 font-normal ml-1">(Wajib)</span></td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded text-xs font-medium ${statusClass}">${statusResep}</span>
                        </td>
                        <td class="px-4 py-3 text-center">${aksiHtml}</td>
                    `;
                    daftarContainer.appendChild(row);
                } else {
                    if (komp.opsi && komp.opsi.length > 0) {
                        komp.opsi.forEach(opsi => {
                            let targetMenuId = opsi.menu_id;
                            let namaMenu = opsi.nama_pilihan;
                            let kategori = '-';
                            if (opsi.menu) {
                                namaMenu = opsi.menu.nama_menu || opsi.nama_pilihan;
                                kategori = opsi.menu.kategori_menu ? opsi.menu.kategori_menu.nama_kategori : '-';
                            } else if (!targetMenuId) {
                                // Fallback for old data where opsi.menu_id is null
                                const found = allMenusData.find(m => m.nama_menu === opsi.nama_pilihan || m.nama === opsi.nama_pilihan);
                                if (found) targetMenuId = found.id;
                            }
                            
                            let statusResep = 'Belum Lengkap';
                            if (targetMenuId) {
                                const found = allMenusData.find(m => m.id === targetMenuId);
                                if (found && found.resep_menu && found.resep_menu.length > 0) {
                                    statusResep = 'Lengkap';
                                }
                            }
                            const statusClass = statusResep === 'Lengkap' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';

                            let aksiHtml = '-';
                            if (targetMenuId) {
                                if (statusResep === 'Lengkap') {
                                    aksiHtml = `
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" onclick="openMenuModal(${targetMenuId}, false, 'resep')" class="text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"></path><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path></svg>
                                                Edit Resep
                                            </button>
                                            <button type="button" onclick="deleteResep(${targetMenuId})" class="text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                Hapus Resep
                                            </button>
                                        </div>
                                    `;
                                } else {
                                    aksiHtml = `
                                        <button type="button" onclick="openMenuModal(${targetMenuId}, false, 'resep')" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                                            Tambahkan Resep
                                        </button>
                                    `;
                                }
                            } else {
                                // For options that don't have a menu yet
                                aksiHtml = `
                                    <button type="button" onclick="createMenuForOption(${opsi.id}, '${namaMenu.replace(/'/g, "\\'")}')" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                                        Tambahkan Resep
                                    </button>
                                `;
                            }

                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td class="px-4 py-3 text-gray-500">${no++}</td>
                                <td class="px-4 py-3 font-medium text-gray-900">${namaMenu} <span class="text-xs text-gray-400 font-normal ml-1">(${komp.nama_item})</span></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-1 rounded text-xs font-medium ${statusClass}">${statusResep}</span>
                                </td>
                                <td class="px-4 py-3 text-center">${aksiHtml}</td>
                            `;
                            daftarContainer.appendChild(row);
                        });
                    }
                }
            });
            } else {
                daftarEmpty.classList.remove('hidden');
            }
        }
    } else {
        const btnInformasi = document.getElementById('tabBtnInformasi');
        btnResep.classList.remove('hidden');
        btnDaftarMenuPaket.classList.add('hidden');
        btnInformasi.textContent = 'Informasi Menu';
        btnResep.textContent = 'Resep Bahan Baku';
        if (komponenContainerEdit) komponenContainerEdit.classList.add('hidden');
        if (komponenContainerView) komponenContainerView.classList.add('hidden');
    }

    // Reset komponen builder (Alpine)
    window.dispatchEvent(new CustomEvent('set-readonly', { detail: isView }));
    window.dispatchEvent(new CustomEvent('set-komponens', { detail: (menu && menu.komponen_paket) ? menu.komponen_paket : [] }));
    renderKomponenViewList((menu && menu.komponen_paket) ? menu.komponen_paket : []);

    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    overlay.classList.remove('hidden');
    switchMenuTab(defaultTab);
    requestAnimationFrame(() => {
        overlay.classList.remove('opacity-0');
        panel.classList.remove('translate-x-full');
    });
}

function renderKomponenViewList(komponenList) {
    const container = document.getElementById('viewKomponenList');
    if (!container) return;
    container.innerHTML = '';

    if (!komponenList || komponenList.length === 0) {
        container.innerHTML = '<div class="text-xs text-gray-400 italic py-1">Belum ada item paket.</div>';
        return;
    }

    let html = '<div class="space-y-3 py-1">';
    komponenList.forEach((komp, idx) => {
        const namaKelompok = komp.nama_item || komp.nama_komponen || (komp.menu_terkait ? komp.menu_terkait.nama_menu : 'Kelompok ' + (idx + 1));
        const opsiList = komp.opsi || komp.pilihan || komp.pilihan_item_paket || [];
        
        let opsiText = '';
        if (opsiList && opsiList.length > 0) {
            opsiText = opsiList.map(o => o.nama_pilihan || (o.menu ? o.menu.nama_menu : '') || o.nama || '').filter(Boolean).join(', ');
        }

        html += `
            <div class="space-y-0.5">
                <div class="font-bold text-sm text-gray-900">${namaKelompok}</div>
                ${opsiText ? `<div class="text-xs text-gray-500 font-medium">• ${opsiText}</div>` : ''}
            </div>
        `;
    });
    html += '</div>';
    container.innerHTML = html;
}

function renderPaketResepTree(menu) {
    const container = document.getElementById('paketTreeViewContainer');
    if (!container) return;

    const menuNama = menu ? (menu.nama_menu || menu.nama || 'Paket') : 'Paket';
    const isBancakan = menuNama.toLowerCase().includes('bancakan');
    const subtitleText = isBancakan 
        ? 'Komposisi resep bahan baku per porsi paket (Porsi 5 Orang)' 
        : 'Komposisi resep bahan baku per porsi menu paket';

    let totalItems = 0;
    let siapCount = 0;
    let nodesHtml = '';

    if (menu && menu.komponen_paket && menu.komponen_paket.length > 0) {
        menu.komponen_paket.forEach((komp) => {
            const isTetap = komp.tipe_item === 'tetap' || komp.tipe_item === 'wajib';

            let renderNode = (targetMenuId, namaItem, categoryBadge) => {
                totalItems++;
                let foundMenu = null;
                if (targetMenuId) {
                    foundMenu = allMenusData.find(m => m.id === targetMenuId);
                } else {
                    foundMenu = allMenusData.find(m => m.nama_menu === namaItem || m.nama === namaItem);
                }

                let resepList = foundMenu ? (foundMenu.resep_menu || []) : [];
                let hasResep = resepList.length > 0;
                if (hasResep) siapCount++;

                let nodeHtml = `
                    <div class="bg-white border border-gray-200/80 hover:border-gray-300 rounded-2xl p-4 shadow-xs transition-all flex flex-col justify-between">
                        <div>
                            {{-- Card Header --}}
                            <div class="flex items-center justify-between pb-3 border-b border-gray-100 gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full ${hasResep ? 'bg-emerald-500 ring-4 ring-emerald-50' : 'bg-amber-400 ring-4 ring-amber-50'} shrink-0"></span>
                                    <h4 class="font-bold text-gray-900 text-sm truncate">${namaItem}</h4>
                                    <span class="text-[10px] font-semibold text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md shrink-0">${categoryBadge}</span>
                                </div>
                                <span class="text-[11px] font-bold shrink-0 ${hasResep ? 'text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-100' : 'text-amber-700 bg-amber-50 px-2.5 py-0.5 rounded-full border border-amber-100'}">
                                    ${hasResep ? `${resepList.length} Bahan` : 'Belum Ada Resep'}
                                </span>
                            </div>

                            {{-- Resep Ingredients List --}}
                            <div class="pt-3">
                `;

                if (hasResep) {
                    nodeHtml += `
                                <div class="space-y-1">
                    `;
                    resepList.forEach(r => {
                        let namaBahan = r.bahan_baku ? (r.bahan_baku.nama_bahan || r.bahan_baku.nama) : 'Bahan Baku';
                        let satStr = '';
                        if (r.satuan && (r.satuan.singkatan || r.satuan.nama_satuan)) {
                            satStr = r.satuan.singkatan || r.satuan.nama_satuan;
                        } else if (r.bahan_baku && r.bahan_baku.satuan && (r.bahan_baku.satuan.singkatan || r.bahan_baku.satuan.nama_satuan)) {
                            satStr = r.bahan_baku.satuan.singkatan || r.bahan_baku.satuan.nama_satuan;
                        }
                        let jumlah = r.jumlah_kebutuhan || r.jumlah || 0;
                        let formattedJumlah = parseFloat(jumlah).toLocaleString('id-ID');

                        nodeHtml += `
                                    <div class="flex items-center justify-between py-1 px-2 rounded-lg text-xs hover:bg-gray-50/80 transition-colors border border-transparent hover:border-gray-100">
                                        <span class="text-gray-700 font-medium flex items-center gap-2 truncate pr-2">
                                            <span class="w-1.5 h-1.5 rounded-full bg-gray-300 shrink-0"></span>
                                            <span class="truncate">${namaBahan}</span>
                                        </span>
                                        <span class="font-mono text-xs font-bold text-gray-900 bg-gray-100/90 px-2 py-0.5 rounded-md border border-gray-200/60 shrink-0">
                                            ${formattedJumlah} <span class="font-sans text-[11px] font-semibold text-gray-500">${satStr}</span>
                                        </span>
                                    </div>
                        `;
                    });
                    nodeHtml += `
                                </div>
                    `;
                } else {
                    nodeHtml += `
                                <div class="py-4 text-center bg-amber-50/50 rounded-xl border border-dashed border-amber-200">
                                    <span class="text-xs text-amber-600 font-medium italic">Belum ada rincian bahan baku untuk menu ini</span>
                                </div>
                    `;
                }

                nodeHtml += `
                            </div>
                        </div>
                    </div>
                `;
                return nodeHtml;
            };

            if (isTetap) {
                let targetMenuId = komp.menu_terkait ? komp.menu_terkait.id : null;
                let namaMenu = komp.menu_terkait ? (komp.menu_terkait.nama_menu || komp.nama_item) : komp.nama_item;
                let kat = komp.menu_terkait && komp.menu_terkait.kategori_menu ? komp.menu_terkait.kategori_menu.nama_kategori : 'Wajib';
                nodesHtml += renderNode(targetMenuId, namaMenu, kat);
            } else if (komp.opsi && komp.opsi.length > 0) {
                komp.opsi.forEach(opsi => {
                    let targetMenuId = opsi.menu_id;
                    let namaMenu = opsi.menu ? (opsi.menu.nama_menu || opsi.nama_pilihan) : opsi.nama_pilihan;
                    let kat = opsi.menu && opsi.menu.kategori_menu ? opsi.menu.kategori_menu.nama_kategori : 'Pilihan';
                    nodesHtml += renderNode(targetMenuId, namaMenu, kat);
                });
            }
        });
    }

    let html = `
        <div class="space-y-4 font-sans">
            {{-- Header Card --}}
            <div class="bg-white border border-gray-200/90 rounded-2xl p-4 sm:p-5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-bold text-gray-900 text-base sm:text-lg tracking-tight">${menuNama}</h3>
                        ${isBancakan ? '<span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">Porsi 5 Orang</span>' : ''}
                    </div>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">${subtitleText}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="px-3 py-1 rounded-xl bg-gray-50 text-gray-700 text-xs font-bold border border-gray-200">
                        ${totalItems} Item Menu
                    </span>
                    <span class="px-3 py-1 rounded-xl bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                        ${siapCount} Resep Lengkap
                    </span>
                </div>
            </div>

            {{-- 2-Column Responsive Grid --}}
            ${totalItems > 0 
                ? `<div class="grid grid-cols-1 md:grid-cols-2 gap-4">${nodesHtml}</div>`
                : `<div class="bg-white border border-gray-200 rounded-2xl p-8 text-center text-gray-400 text-sm">Belum ada menu dalam paket ini.</div>`
            }
        </div>
    `;

    container.innerHTML = html;
}

function switchMenuTab(tabId) {
    if (tabId === 'resep' && document.getElementById('tabBtnResep')?.classList.contains('hidden') && !document.getElementById('tabBtnDaftarMenuPaket')?.classList.contains('hidden')) {
        tabId = 'daftar_menu_paket';
    } else if (tabId === 'daftar_menu_paket' && document.getElementById('tabBtnDaftarMenuPaket')?.classList.contains('hidden') && !document.getElementById('tabBtnResep')?.classList.contains('hidden')) {
        tabId = 'resep';
    }

    const tabs = ['informasi', 'resep', 'daftar_menu_paket', 'kebutuhan_paket'];
    
    tabs.forEach(id => {
        const btn = document.getElementById('tabBtn' + id.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(''));
        const content = document.getElementById('tabContent' + id.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(''));
        
        if (!btn || !content) return;

        const isHidden = btn.classList.contains('hidden');
        const ml4 = btn.classList.contains('ml-4') ? ' ml-4' : '';
        const hiddenClass = isHidden ? ' hidden' : '';

        if (tabId === id) {
            btn.className = 'py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-gray-900 text-gray-900' + ml4 + hiddenClass;
            content.classList.remove('hidden');
        } else {
            btn.className = 'py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' + ml4 + hiddenClass;
            content.classList.add('hidden');
        }
    });
}

const allMenusData = @json($allMenusData ?? []);

function openResepDetailModal(menuId) {
    const menu = allMenusData.find(m => m.id == menuId);
    if (!menu) return;

    // Populate Info
    document.getElementById('rdmKodeMenu').textContent = menu.id_menu || '-';
    document.getElementById('rdmNamaMenu').textContent = menu.nama_menu || menu.nama || '-';
    document.getElementById('rdmKategori').textContent = menu.kategori_menu ? menu.kategori_menu.nama_kategori : '-';

    // Populate Table
    const tbody = document.getElementById('rdmTableBody');
    const emptyState = document.getElementById('rdmEmptyState');
    tbody.innerHTML = '';
    
    if (menu.resep_menu && menu.resep_menu.length > 0) {
        emptyState.classList.add('hidden');
        let no = 1;
        menu.resep_menu.forEach(r => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            
            const bahan = bahanBakusData.find(b => b.id == r.bahan_baku_id);
            const namaBahan = bahan ? bahan.nama_bahan : '-';
            const satuanStr = r.satuan ? (r.satuan.singkatan || r.satuan.nama_satuan) : (bahan && bahan.satuan ? (bahan.satuan.singkatan || bahan.satuan.nama_satuan) : '-');
            
            row.innerHTML = `
                <td class="px-5 py-3 text-gray-500">${no++}</td>
                <td class="px-5 py-3 font-medium text-gray-900">${namaBahan}</td>
                <td class="px-5 py-3 font-medium text-gray-900 text-right">${fmtJml(r.jumlah_kebutuhan || r.jumlah || 0)}</td>
                <td class="px-5 py-3 text-gray-500">${satuanStr}</td>
            `;
            tbody.appendChild(row);
        });
    } else {
        emptyState.classList.remove('hidden');
    }

    const overlay = document.getElementById('resepDetailModalOverlay');
    const panel = document.getElementById('resepDetailModalPanel');
    
    overlay.classList.remove('hidden');
    panel.classList.remove('hidden');
    requestAnimationFrame(() => {
        overlay.classList.remove('opacity-0');
        panel.classList.remove('translate-x-full');
    });
}

function closeResepDetailModal() {
    const overlay = document.getElementById('resepDetailModalOverlay');
    const panel = document.getElementById('resepDetailModalPanel');
    
    overlay.classList.add('opacity-0');
    panel.classList.add('translate-x-full');
    
    setTimeout(() => {
        overlay.classList.add('hidden');
        panel.classList.add('hidden');
    }, 300);
}

const bahanBakusData = @json($bahanBakus ?? []);
function checkResepEmptyState() {
    const container = document.getElementById('resepContainer');
    const emptyState = document.getElementById('resepEmptyState');
    if (container.children.length === 0) {
        emptyState.classList.remove('hidden');
    } else {
        emptyState.classList.add('hidden');
    }
}

function handleAddResepCustom() {
    const sel = document.getElementById('inputBahanBaku');
    const jml = document.getElementById('inputJumlah');
    const sat = document.getElementById('inputSatuan');
    const bahanId = sel.value;
    const jumlah = jml.value;
    const satuanId = sat.value;
    
    if (!bahanId) {
        alert('Silakan pilih bahan baku terlebih dahulu.');
        return;
    }
    if (!jumlah || parseFloat(jumlah) <= 0) {
        alert('Jumlah kebutuhan bahan harus berupa angka positif (lebih dari 0).');
        return;
    }
    
    const bb = bahanBakusData.find(b => b.id == bahanId);
    let satuanText = bb && bb.satuan ? (bb.satuan.singkatan || bb.satuan.nama_satuan) : 'pcs';
    
    addResepRow(bahanId, jumlah, satuanId, satuanText, false);
    
    window.dispatchEvent(new CustomEvent('reset-resep-input'));
}

function handleAddResep() {
    handleAddResepCustom();
}

function editResepRow(btn, bahanId, jumlah, satuanId) {
    window.dispatchEvent(new CustomEvent('edit-resep-input', { detail: { bahanId: bahanId, jumlah: jumlah } }));
    btn.closest('tr').remove();
    checkResepEmptyState();
}

// Format jumlah: hilangkan trailing zeros (500.000 → 500, 0.500 → 0,5)
function fmtJml(v) {
    let n = parseFloat(v);
    if (isNaN(n)) return '0';
    // Format ke max 3 desimal, lalu buang trailing zeros
    let s = n.toFixed(3).replace(/\.?0+$/, '');
    return s.replace('.', ',');
}

function addResepRow(bahanId, jumlah, satuanId = null, satuanText = null, isView = false) {
    const container = document.getElementById('resepContainer');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50 transition-colors bg-white';
    
    const bb = bahanBakusData.find(b => b.id == bahanId);
    if (!bb) return;
    const namaBahan = bb.nama_bahan;
    
    if (!satuanText) {
        satuanText = bb.satuan ? (bb.satuan.singkatan || bb.satuan.nama_satuan) : '';
    }
    
    let aksiHtml = '';
    if (!isView) {
        aksiHtml = `
            <button type="button" onclick="editResepRow(this, ${bahanId}, ${jumlah}, ${satuanId || null})" class="px-2 py-1 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors inline-flex items-center gap-1">Edit</button>
            <button type="button" onclick="this.closest('tr').remove(); checkResepEmptyState();" class="px-2 py-1 text-xs font-semibold text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors inline-flex items-center gap-1 ml-1">Hapus</button>
        `;
    }

    tr.innerHTML = `
        <td class="px-5 py-3 font-medium text-gray-900">${namaBahan}
            ${!isView ? `<input type="hidden" name="bahan_baku_id[]" value="${bahanId}">` : ''}
        </td>
        <td class="px-5 py-3 font-medium text-gray-900 text-right">${fmtJml(jumlah)}
            ${!isView ? `<input type="hidden" name="jumlah_kebutuhan[]" value="${jumlah}">` : ''}
        </td>
        <td class="px-5 py-3 text-gray-500">${satuanText}
            ${!isView ? `<input type="hidden" name="satuan_id[]" value="${satuanId || ''}">` : ''}
        </td>
        ${!isView ? `<td class="px-5 py-3 text-center align-middle">${aksiHtml}</td>` : '<td style="display:none;"></td>'}
    `;
    container.appendChild(tr);
    checkResepEmptyState();
}

function toggleSatuanBaruForm() {
    const form = document.getElementById('satuanBaruForm');
    if (form.classList.contains('hidden')) {
        form.classList.remove('hidden');
        document.getElementById('inputNamaSatuanBaru').focus();
    } else {
        form.classList.add('hidden');
        document.getElementById('inputNamaSatuanBaru').value = '';
        document.getElementById('inputSingkatanSatuanBaru').value = '';
    }
}

async function simpanSatuanBaru() {
    const namaInput = document.getElementById('inputNamaSatuanBaru');
    const singkatanInput = document.getElementById('inputSingkatanSatuanBaru');
    const nama = namaInput.value.trim();
    const singkatan = singkatanInput.value.trim();
    
    if (!nama) {
        alert('Nama satuan tidak boleh kosong!');
        namaInput.focus();
        return;
    }
    
    try {
        const res = await fetch('{{ route("satuan.ajax.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ nama_satuan: nama, singkatan: singkatan })
        });
        
        if (res.ok) {
            const data = await res.json();
            const sel = document.getElementById('inputSatuan');
            const opt = document.createElement('option');
            opt.value = data.id;
            opt.textContent = data.singkatan || data.nama_satuan;
            sel.appendChild(opt);
            sel.value = data.id;
            toggleSatuanBaruForm();
        } else {
            alert('Gagal menambahkan satuan.');
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
    }
}

function closeMenuModal() {
    const drawer = document.getElementById('drawerMenu');
    const panel = document.getElementById('drawerMenuPanel');
    const overlay = document.getElementById('drawerMenuOverlay');
    panel.classList.add('translate-x-full');
    overlay.classList.add('opacity-0');
    setTimeout(() => {
        drawer.classList.add('hidden');
        overlay.classList.add('hidden');
        drawer.style.display = '';
    }, 300);
}

function filterKat(jenis) {
    const sel = document.getElementById('mnKategori');
    const opts = sel.querySelectorAll('option');
    opts.forEach(o => {
        if (!o.value) return;
        const dj = o.getAttribute('data-jenis');
        let match = false;
        if (!jenis || !dj) {
            match = true;
        } else if (jenis == '1' || jenis === 'dine_in') {
            match = (!dj || dj == '1' || dj === 'dine_in');
        } else if (jenis == '2' || jenis === 'catering') {
            match = (!dj || dj == '2' || dj === 'catering');
        } else if (jenis == '3' || jenis === 'nasi_box') {
            match = (!dj || dj == '3' || dj === 'nasi_box');
        } else {
            match = (!dj || dj == jenis);
        }
        o.style.display = match ? '' : 'none';
    });
    if (sel.options[sel.selectedIndex]?.style.display === 'none') sel.value = '';
}

// ═══ KATEGORI DRAWER (SLIDE-IN RIGHT) ═══
function openKategoriModal(id = null, nama = '', deskripsi = '') {
    const drawer = document.getElementById('drawerKategori');
    const panel = document.getElementById('drawerKategoriPanel');

    document.getElementById('katModalTitle').textContent = id ? 'Edit Kategori' : 'Tambah Kategori';
    document.getElementById('formKategori').action = id ? `${BASE_URL}/kategori-menu/${id}` : '{{ route("kategori-menu.store") }}';
    document.getElementById('formKatMethod').innerHTML = id ? '<input type="hidden" name="_method" value="PUT">' : '';
    document.getElementById('katNama').value = nama;
    document.getElementById('katDeskripsi').value = deskripsi !== '-' ? deskripsi : '';

    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    requestAnimationFrame(() => {
        panel.classList.remove('translate-x-full');
    });
}

function closeKategoriModal() {
    const drawer = document.getElementById('drawerKategori');
    const panel = document.getElementById('drawerKategoriPanel');

    panel.classList.add('translate-x-full');
    setTimeout(() => {
        drawer.classList.add('hidden');
        drawer.style.display = '';
    }, 300);
}

// ═══ MULTI-TAB FORM VALIDATION HANDLER ═══
// Removed: using novalidate on form to rely on Laravel validation instead
</script>
@endsection
