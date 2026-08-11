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
                    <button onclick="openKategoriModal()" id="btnAddKategori" class="hidden inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Kategori Baru
                    </button>
                    <button onclick="openMenuModal()" id="btnAddMenu" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Menu Baru
                    </button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- TABS --}}
        <x-ui.tab-list>
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
                <form action="{{ route('menu.index') }}" method="GET" class="w-full flex items-center gap-3">
                    <input type="hidden" name="jenis_menu_id" value="{{ request('jenis_menu_id') }}">
                    <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode…" width="w-full sm:w-72" />
                    <select name="filter_resep" onchange="this.form.submit()" class="h-10 px-3 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 bg-white transition-all text-gray-700">
                        <option value="">Resep Menu</option>
                        <option value="ada" {{ request('filter_resep') == 'ada' ? 'selected' : '' }}>Sudah Ada Resep</option>
                        <option value="belum" {{ request('filter_resep') == 'belum' ? 'selected' : '' }}>Belum Ada Resep</option>
                    </select>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[820px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No</th>
                    <th class="px-4 py-3.5 text-left">Foto</th>
                    <th class="px-4 py-3.5 text-left">Nama Menu</th>
                    <th class="px-4 py-3.5 text-left">Kategori</th>
                    <th class="px-4 py-3.5 text-left">Harga</th>
                    @if($jenisId != 1)
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
                                                        <td class="px-4 py-3 text-sm text-gray-900">
                                Rp{{ number_format($menu->harga_jual, 0, ',', '.') }}
                                @if($menu->jenis_menu_id == '2' || $menu->jenis_menu_id == 'catering')
                                    /porsi
                                @endif
                            </td>
                            @if($jenisId != 1)
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
                        <x-empty-state icon="archive-box" title="Belum ada menu" message="Tambahkan menu untuk mulai melayani pelanggan." :colspan="7" />
                        @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>


    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: TAMBAH/EDIT MENU (SLIDE-IN RIGHT) --}}
{{-- ══════════════════════════════════════════ --}}
<div id="drawerMenu" class="fixed inset-x-0 bottom-0 top-16 z-40 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-sm opacity-0 transition-opacity duration-300" id="drawerMenuOverlay" onclick="closeMenuModal()"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-lg bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerMenuPanel">
        
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
        </div>

        {{-- Form Body --}}
        <form id="formMenu" action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto flex flex-col justify-between">
            @csrf
            <div id="formMenuMethod"></div>
            
            {{-- Tab: Informasi --}}
            <div id="tabContentInformasi" class="px-5 py-5 space-y-4">
                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Menu <span class="text-red-500">*</span></label>
                    <textarea name="nama" id="mnNama" rows="2" required placeholder="Contoh: Ayam Bakar Madu" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all resize-none"></textarea>
                </div>

                {{-- Komponen Paket Section (Hanya untuk Catering & Nasi Box) --}}
                @if($jenisId != 1)
                <div class="border border-gray-100 bg-gray-50/50 p-4 rounded-xl space-y-4">
                    @include('admin.menu.paket.partials.komponen-builder', ['existingKomponen' => []])
                </div>
                @endif

                {{-- Layanan + Kategori --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Layanan <span class="text-red-500">*</span></label>
                        <select name="jenis_menu_id" id="mnJenis" required onchange="filterKat(this.value)" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="1">Dine in</option>
                            <option value="2">Katering</option>
                            <option value="3">Nasi Box</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori_menu_id" id="mnKategori" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="">— Pilih Kategori —</option>
                            @foreach($allKategoris ?? $kategoris as $kat)
                                <option value="{{ $kat->id }}" data-jenis="{{ $kat->jenis_menu_id ?? '' }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Harga + Status --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="harga" id="mnHarga" required min="0" placeholder="25000" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all font-medium">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="mnStatus" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="tersedia">Aktif (Tersedia)</option>
                            <option value="habis">Stok Habis</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" id="mnDeskripsi" rows="2" placeholder="Penjelasan singkat mengenai menu…" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all resize-none"></textarea>
                </div>

                {{-- Foto --}}
                <div id="mnFotoContainer">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Foto Menu (Opsional)</label>
                    <input type="file" id="mnFoto" name="foto" accept="image/*" class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                </div>
                

            </div>
            
            {{-- Tab: Resep --}}
            <div id="tabContentResep" class="px-5 py-5 space-y-4 hidden">
                <div id="resepInputArea" class="bg-gray-50 p-4 rounded-lg border border-gray-100 space-y-3">
                    <p class="text-xs text-gray-500">Masukkan bahan baku yang dibutuhkan untuk 1 porsi menu ini.</p>
                    <div class="flex gap-2 items-start">
                        <div class="flex-1">
                            <select id="inputBahanBaku" onchange="document.getElementById('inputSatuan').value = this.options[this.selectedIndex].getAttribute('data-satuan_id') || ''" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded bg-white outline-none">
                                <option value="" data-satuan_id="" data-nama="">Pilih Bahan</option>
                                @foreach($bahanBakus ?? [] as $bb)
                                <option value="{{ $bb->id }}" data-satuan_id="{{ $bb->satuan_id }}" data-nama="{{ $bb->nama_bahan }}">{{ $bb->nama_bahan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="w-48 flex items-center border border-gray-200 rounded overflow-hidden bg-white">
                            <input type="number" step="0.01" id="inputJumlah" placeholder="Jumlah" class="w-16 px-2 py-1.5 text-xs outline-none bg-transparent">
                            <div class="w-px h-5 bg-gray-200"></div>
                            <select id="inputSatuan" class="flex-1 px-1 py-1.5 text-xs text-gray-500 bg-transparent outline-none cursor-pointer text-center border-0 focus:ring-0">
                                <option value="">Satuan</option>
                                @foreach($satuans ?? [] as $st)
                                <option value="{{ $st->id }}">{{ $st->singkatan ?? $st->nama_satuan }}</option>
                                @endforeach
                            </select>
                            <button type="button" onclick="toggleSatuanBaruForm()" class="w-7 h-full min-h-[32px] flex items-center justify-center bg-gray-50 hover:bg-gray-100 text-gray-600 border-l border-gray-200 transition-colors" title="Tambah Satuan Baru">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                        <div>
                            <button type="button" onclick="handleAddResep()" class="inline-flex items-center justify-center h-8 px-3 text-xs font-semibold bg-gray-900 text-white rounded hover:bg-gray-800 transition-colors">
                                Tambah
                            </button>
                        </div>
                    </div>
                    
                    {{-- Form Tambah Satuan Baru (Inline) --}}
                    <div id="satuanBaruForm" class="hidden mt-3 p-3 bg-white border border-gray-200 rounded-lg shadow-sm">
                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nama Satuan</label>
                                <input type="text" id="inputNamaSatuanBaru" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded outline-none" placeholder="Contoh: Ikat">
                            </div>
                            <div class="w-24">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Singkatan</label>
                                <input type="text" id="inputSingkatanSatuanBaru" class="w-full px-2 py-1.5 text-xs border border-gray-200 rounded outline-none" placeholder="Contoh: ikt">
                            </div>
                            <button type="button" onclick="simpanSatuanBaru()" class="px-3 py-1.5 text-xs font-semibold bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors">
                                Simpan Satuan
                            </button>
                            <button type="button" onclick="toggleSatuanBaruForm()" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 rounded hover:bg-gray-200 transition-colors">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-2">Daftar Resep</h4>
                    <div id="resepContainer" class="flex flex-col border border-gray-100 rounded-lg overflow-hidden bg-white divide-y divide-gray-100">
                        <!-- Resep rows will be populated here -->
                    </div>
                    <div id="resepEmptyState" class="text-center py-6 text-gray-400 text-sm">
                        Belum ada bahan baku.
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <button type="button" id="btnBatalMenu" onclick="closeMenuModal()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" id="btnSimpanMenu" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-5 py-2 hover:bg-gray-800 transition-colors">Simpan Menu</button>
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" id="katNama" required placeholder="Contoh: Makanan Utama" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" id="katDeskripsi" placeholder="Tuliskan deskripsi kategori..." rows="3" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all"></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <button type="button" onclick="closeKategoriModal()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-5 py-2 hover:bg-gray-800 transition-colors">Simpan Kategori</button>
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
            <div class="flex gap-3 justify-center pt-1">
                <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 text-sm font-semibold text-white bg-red-500 rounded-xl px-4 py-2.5 hover:bg-red-600 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 text-sm font-semibold text-white bg-gray-900 rounded-xl px-4 py-2.5 hover:bg-gray-800 transition-colors">
                    Ya, Hapus
                </button>
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

// ═══ MENU DRAWER ═══
function openMenuModal(menuId = null, isView = false, defaultTab = 'informasi') {
    let menu = null;
    if (menuId) {
        menu = menusData.find(m => m.id == menuId);
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

    const jenisVal = menu ? (menu.jenis_menu_id ?? 1) : 1;
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
        document.getElementById('mnFotoContainer').classList.add('hidden');
        document.getElementById('btnSimpanMenu').classList.add('hidden');
        document.getElementById('btnBatalMenu').textContent = 'Tutup';
    } else {
        document.getElementById('formMenu').classList.remove('view-mode');
        document.getElementById('mnFotoContainer').classList.remove('hidden');
        document.getElementById('btnSimpanMenu').classList.remove('hidden');
        document.getElementById('btnBatalMenu').textContent = 'Batal';
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
    if (isView) {
        document.getElementById('resepInputArea').classList.add('hidden');
    } else {
        document.getElementById('resepInputArea').classList.remove('hidden');
    }

    // Reset komponen builder (Alpine)
    window.dispatchEvent(new CustomEvent('set-readonly', { detail: isView }));
    window.dispatchEvent(new CustomEvent('set-komponens', { detail: (menu && menu.komponen_paket) ? menu.komponen_paket : [] }));

    drawer.classList.remove('hidden');
    drawer.style.display = 'flex';
    overlay.classList.remove('hidden');
    switchMenuTab(defaultTab);
    requestAnimationFrame(() => {
        overlay.classList.remove('opacity-0');
        panel.classList.remove('translate-x-full');
    });
}

function switchMenuTab(tabId) {
    const btnInfo = document.getElementById('tabBtnInformasi');
    const btnResep = document.getElementById('tabBtnResep');
    const contentInfo = document.getElementById('tabContentInformasi');
    const contentResep = document.getElementById('tabContentResep');

    if (tabId === 'informasi') {
        btnInfo.className = 'py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-gray-900 text-gray-900';
        btnResep.className = 'py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 ml-4';
        contentInfo.classList.remove('hidden');
        contentResep.classList.add('hidden');
    } else {
        btnInfo.className = 'py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300';
        btnResep.className = 'py-2.5 px-3 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none border-gray-900 text-gray-900 ml-4';
        contentInfo.classList.add('hidden');
        contentResep.classList.remove('hidden');
    }
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

function handleAddResep() {
    const sel = document.getElementById('inputBahanBaku');
    const jml = document.getElementById('inputJumlah');
    const sat = document.getElementById('inputSatuan');
    const bahanId = sel.value;
    const jumlah = jml.value;
    const satuanId = sat.value;
    
    if (!bahanId || !jumlah || !satuanId) return;
    
    const satuanText = sat.options[sat.selectedIndex].text;
    
    addResepRow(bahanId, jumlah, satuanId, satuanText, false);
    
    sel.value = '';
    jml.value = '';
    sat.value = '';
}

function editResepRow(btn, bahanId, jumlah, satuanId) {
    const sel = document.getElementById('inputBahanBaku');
    sel.value = bahanId;
    document.getElementById('inputJumlah').value = jumlah;
    document.getElementById('inputSatuan').value = satuanId;
    btn.closest('.resep-row').remove();
    checkResepEmptyState();
}

function addResepRow(bahanId, jumlah, satuanId = null, satuanText = null, isView = false) {
    const container = document.getElementById('resepContainer');
    const div = document.createElement('div');
    div.className = 'flex items-center justify-between p-3 resep-row hover:bg-gray-50 transition-colors text-sm';
    
    const bb = bahanBakusData.find(b => b.id == bahanId);
    if (!bb) return;
    const namaBahan = bb.nama_bahan;
    
    div.innerHTML = `
        <div class="flex-1 font-medium text-gray-900">${namaBahan}</div>
        <div class="w-32 flex items-center justify-end gap-1.5 text-gray-600">
            <span class="font-semibold text-gray-900">${jumlah}</span>
            <span class="text-xs">${satuanText || ''}</span>
        </div>
        ${!isView ? `
        <div class="w-24 flex justify-end items-center gap-3 border-l border-gray-200 ml-4 pl-4">
            <input type="hidden" name="bahan_baku_id[]" value="${bahanId}">
            <input type="hidden" name="jumlah_kebutuhan[]" value="${jumlah}">
            <input type="hidden" name="satuan_id[]" value="${satuanId}">
            <button type="button" onclick="editResepRow(this, '${bahanId}', '${jumlah}', '${satuanId}')" class="text-blue-600 hover:text-blue-800 text-xs font-semibold">Edit</button>
            <button type="button" onclick="this.closest('.resep-row').remove(); checkResepEmptyState();" class="text-red-600 hover:text-red-800 text-xs font-semibold">Hapus</button>
        </div>
        ` : ''}
    `;
    
    container.appendChild(div);
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
</script>
@endsection
