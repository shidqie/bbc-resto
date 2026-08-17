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
                    <x-ui.button variant="primary" icon="plus" onclick="openMenuModal()" id="btnAddMenu">
                        Menu Baru
                    </x-ui.button>
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
                            </td>
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
                        <tr>
                            <td colspan="{{ $jenisId != 1 ? 7 : 6 }}">
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
        <form id="formMenu" action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="flex-1 overflow-y-auto flex flex-col justify-between">
            @csrf
            <div id="formMenuMethod"></div>
            
            {{-- Tab: Informasi --}}
            <div id="tabContentInformasi" class="px-5 py-5">
                
                {{-- Mode Edit / Create --}}
                <div id="infoEditMode" class="space-y-4">
                    {{-- Nama --}}
                    <div>
                        <x-ui.textarea name="nama" id="mnNama" label="Nama Menu *" rows="2" required placeholder="Contoh: Ayam Bakar Madu" />
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
                            <x-ui.input type="number" name="harga" id="mnHarga" label="Harga (Rp) *" required min="0" placeholder="25000" class="font-medium" />
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
                        <div class="rounded-xl overflow-hidden bg-gray-50 aspect-square md:aspect-auto md:h-[280px] flex items-center justify-center">
                            <img id="viewFotoMenu" src="" class="w-full h-full object-cover hidden" alt="Foto">
                            <span id="viewFotoPlaceholder" class="text-gray-400 text-sm">Tidak ada foto</span>
                        </div>
                    </div>
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
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 mb-1">Daftar Resep Menu Paket</h4>
                    <p class="text-xs text-gray-500 mb-3">Daftar menu yang termasuk dalam paket ini beserta status resepnya.</p>
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                <tr>
                                    <th class="px-4 py-3">No</th>
                                    <th class="px-4 py-3">Nama Menu</th>
                                    <th class="px-4 py-3">Kategori</th>
                                    <th class="px-4 py-3">Tipe</th>
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
    if (isView) {
        document.getElementById('resepInputArea').classList.add('hidden');
        document.getElementById('resepAksiHeader').style.display = 'none';
    } else {
        document.getElementById('resepInputArea').classList.remove('hidden');
        document.getElementById('resepAksiHeader').style.display = '';
    }

    const isPaket = jenisVal == 2 || jenisVal == 3;
    const btnResep = document.getElementById('tabBtnResep');
    const btnDaftarMenuPaket = document.getElementById('tabBtnDaftarMenuPaket');

    if (isPaket) {
        btnResep.classList.add('hidden');
        btnDaftarMenuPaket.classList.remove('hidden');

        // Populate Daftar Menu Paket
        const daftarContainer = document.getElementById('daftarMenuPaketContainer');
        const daftarEmpty = document.getElementById('daftarMenuPaketEmpty');
        daftarContainer.innerHTML = '';
        
        if (menu && menu.komponen_paket && menu.komponen_paket.length > 0) {
            daftarEmpty.classList.add('hidden');
            let no = 1;
            menu.komponen_paket.forEach(komp => {
                    const statusResep = (menu.status_resep_komponen && menu.status_resep_komponen[komp.id]) || 'Belum Lengkap';
                    const statusClass = statusResep === 'Lengkap' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700';
                    const isTetap = komp.tipe_item === 'tetap';
                    
                    let namaMenu = komp.nama_item;
                    let kategori = '-';
                    let tipeText = isTetap ? 'Wajib' : 'Pilihan';
                    let aksiHtml = '-';
                    
                    let targetMenuId = null;

                    if (isTetap) {
                        if (komp.menu_terkait) {
                            namaMenu = komp.menu_terkait.nama_menu || komp.nama_item;
                            kategori = komp.menu_terkait.kategori_menu ? komp.menu_terkait.kategori_menu.nama_kategori : '-';
                            targetMenuId = komp.menu_terkait.id;
                        } else {
                            // Fallback for old data without menu_terkait
                            const found = allMenusData.find(m => m.nama_menu === komp.nama_item || m.nama === komp.nama_item);
                            if (found) targetMenuId = found.id;
                        }
                    } else {
                        // For Pilihan, we can use the first option's recipe as a representative, or just link to it
                        if (komp.opsi && komp.opsi.length > 0) {
                            targetMenuId = komp.opsi[0].menu_id;
                            // Fallback for old data where opsi.menu_id is null
                            if (!targetMenuId) {
                                const found = allMenusData.find(m => m.nama_menu === komp.opsi[0].nama_pilihan || m.nama === komp.opsi[0].nama_pilihan);
                                if (found) targetMenuId = found.id;
                            }
                        }
                    }

                    if (targetMenuId) {
                        aksiHtml = `
                            <button type="button" onclick="openResepDetailModal(${targetMenuId})" class="text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-3 py-1.5 rounded text-xs font-medium inline-flex items-center gap-1.5 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                Lihat Resep
                            </button>
                        `;
                    }

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-4 py-3 text-gray-500">${no++}</td>
                        <td class="px-4 py-3 font-medium text-gray-900">${namaMenu}</td>
                        <td class="px-4 py-3 text-gray-600">${kategori}</td>
                        <td class="px-4 py-3 text-gray-600">${tipeText}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-1 rounded text-xs font-medium ${statusClass}">${statusResep}</span>
                        </td>
                        <td class="px-4 py-3 text-center">${aksiHtml}</td>
                    `;
                    daftarContainer.appendChild(row);
                });
            } else {
                daftarEmpty.classList.remove('hidden');
            }
    } else {
        btnResep.classList.remove('hidden');
        btnDaftarMenuPaket.classList.add('hidden');
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
            if (!isHidden) content.classList.remove('hidden');
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
                <td class="px-5 py-3 font-medium text-gray-900 text-right">${r.jumlah_kebutuhan || r.jumlah || 0}</td>
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
    document.getElementById('inputSatuan').value = satuanId || '';
    btn.closest('tr').remove();
    checkResepEmptyState();
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
        <td class="px-5 py-3 font-medium text-gray-900 text-right">${jumlah}
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
</script>
@endsection
