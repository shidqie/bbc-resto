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
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Manajemen Menu</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola menu berdasarkan layanan dan kategorinya.</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openKategoriModal()" id="btnAddKategori" class="hidden inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-2xl px-3 py-2 hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Kategori Baru
                </button>
                <button onclick="openMenuModal()" id="btnAddMenu" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-2xl px-3 py-2 hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Menu Baru
                </button>
            </div>
        </div>

        <x-ui.alert />

        {{-- TABS --}}
        <x-ui.tab-list>
            <x-ui.tab href="{{ route('menu.index') }}" :active="true">
                Menu Dine In
            </x-ui.tab>
            <x-ui.tab href="{{ route('paket-catering.index', ['jenis' => 'nasi_box']) }}">
                Menu Nasi Box
            </x-ui.tab>
            <x-ui.tab href="{{ route('paket-catering.index', ['jenis' => 'catering']) }}">
                Menu Katering
            </x-ui.tab>
        </x-ui.tab-list>

        @if(session('warning_bom'))
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-2xl px-4 py-3 text-sm text-amber-800 shrink-0 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            <span>{{ session('warning_bom') }}</span>
        </div>
        @endif
        
        {{-- Filter bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('menu.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau kode…" class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-2xl focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                    <input type="hidden" name="jenis_menu_id" value="{{ request('jenis_menu_id') }}">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-2xl px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
        </div>
            {{-- Menu Table --}}
            <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                            <th class="px-4 py-3 text-left w-12">No</th>
                            <th class="px-4 py-3 text-left">Kode</th>
                            <th class="px-4 py-3 text-left">Nama Menu</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-left">Harga</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($menus as $menu)
                        <tr class="hover:bg-gray-50/60 transition-colors group">
                            <td class="px-4 py-3 text-sm text-gray-500 font-medium align-middle">
                                {{ $loop->iteration }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 font-medium">
                                {{ $menu->kode_menu ?? 'MNU-'.str_pad($menu->id,3,'0',STR_PAD_LEFT) }}
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
                            <td class="px-4 py-3 text-center text-sm font-medium">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="openMenuModal({{ $menu->id }}, true)" title="Detail" class="w-7 h-7 rounded-full flex items-center justify-center bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                                        <x-heroicon-o-eye class="w-3 h-3" />
                                    </button>
                                    <button onclick="openMenuModal({{ $menu->id }}, false)" title="Ubah" class="w-7 h-7 rounded-full flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-3 h-3" />
                                    </button>
                                    <button onclick="deleteMenu({{ $menu->id }})" title="Hapus" class="w-7 h-7 rounded-full flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                        <x-heroicon-o-trash class="w-3 h-3" />
                                    </button>
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
            <button onclick="closeMenuModal()" class="p-1.5 rounded-2xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
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
                    <input type="text" name="nama" id="mnNama" required placeholder="Contoh: Ayam Bakar Madu" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>

                {{-- Layanan + Kategori --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Layanan <span class="text-red-500">*</span></label>
                        <select name="jenis_menu_id" id="mnJenis" required onchange="filterKat(this.value)" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-2xl bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="1">Dine in</option>
                            <option value="2">Catering</option>
                            <option value="3">Nasi Box</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select name="kategori_menu_id" id="mnKategori" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-2xl bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
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
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="harga" id="mnHarga" required min="0" placeholder="25000" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">Status <span class="text-red-500">*</span></label>
                        <select name="status" id="mnStatus" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-2xl bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                            <option value="tersedia">Aktif (Tersedia)</option>
                            <option value="habis">Stok Habis</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>

                {{-- Deskripsi --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" id="mnDeskripsi" rows="2" placeholder="Penjelasan singkat mengenai menu…" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all resize-none"></textarea>
                </div>

                {{-- Foto --}}
                <div id="mnFotoContainer">
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Foto Menu (Opsional)</label>
                    <input type="file" id="mnFoto" name="foto" accept="image/*" class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer">
                </div>

                {{-- BOM Section --}}
                <div class="pt-2 border-t border-gray-100">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <p class="text-xs font-semibold text-gray-700">Komposisi Bahan Baku (BOM)</p>
                            <p class="text-xs text-gray-400">Bahan untuk 1 porsi/paket</p>
                        </div>
                        <button type="button" id="btnTambahBom" onclick="addBomRow()" class="text-xs font-medium text-gray-700 border border-gray-200 rounded-2xl px-2.5 py-1.5 hover:bg-gray-50 transition-colors flex items-center gap-1">
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
                <button type="button" id="btnBatalMenu" onclick="closeMenuModal()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-2xl px-4 py-2 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" id="btnSimpanMenu" class="text-sm font-semibold text-white bg-gray-900 rounded-2xl px-5 py-2 hover:bg-gray-800 transition-colors">Simpan Menu</button>
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
            <button onclick="closeKategoriModal()" class="p-1 rounded-2xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Form Body --}}
        <form id="formKategori" action="{{ route('kategori-menu.store') }}" method="POST" class="flex-1 overflow-y-auto flex flex-col justify-between">
            @csrf
            <div id="formKatMethod"></div>
            <div class="px-5 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" id="katNama" required placeholder="Contoh: Makanan Utama" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" id="katDeskripsi" placeholder="Tuliskan deskripsi kategori..." rows="3" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-2xl bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all"></textarea>
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <button type="button" onclick="closeKategoriModal()" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-2xl px-4 py-2 hover:bg-gray-50 transition-colors">Batal</button>
                <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-2xl px-5 py-2 hover:bg-gray-800 transition-colors">Simpan Kategori</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════ --}}
{{-- MODAL: KONFIRMASI HAPUS                  --}}
{{-- ══════════════════════════════════════════ --}}
<div id="modalHapus" class="fixed inset-0 z-50 hidden flex items-center justify-center">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-[2.25rem] shadow-xl w-full max-w-sm mx-4 p-6 space-y-4 text-center">
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
                    class="flex-1 text-sm font-semibold text-white bg-red-500 rounded-3xl px-4 py-2.5 hover:bg-red-600 transition-colors">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 text-sm font-semibold text-white bg-gray-900 rounded-3xl px-4 py-2.5 hover:bg-gray-800 transition-colors">
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const menusData = @json($menus->items());
const bahanBakusData = @json($bahanBakus);
const BASE_URL = '{{ url('/') }}';

function deleteMenu(id) {
    document.getElementById('formHapus').action = `${BASE_URL}/menu/${id}`;
    document.getElementById('modalHapus').classList.remove('hidden');
}

function deleteKategori(id) {
    document.getElementById('formHapus').action = `${BASE_URL}/kategori-menu/${id}`;
    document.getElementById('modalHapus').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('modalHapus').classList.add('hidden');
    document.getElementById('formHapus').action = '';
}


// ═══ MENU DRAWER ═══
let isViewMode = false;
function openMenuModal(menuId = null, isView = false) {
    let menu = null;
    let reseps = null;
    if (menuId) {
        menu = menusData.find(m => m.id == menuId);
        if (menu) {
            reseps = menu.resep_menu || [];
        }
    }

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

    if (menu && !isView) {
        document.getElementById('formMenu').action = `${BASE_URL}/menu/${menu.id}`;
        document.getElementById('formMenuMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    }

    // BOM rows
    document.getElementById('bomRows').innerHTML = '';
    if (reseps && reseps.length > 0) {
        reseps.forEach(r => addBomRow(r.bahan_baku_id, r.jumlah ?? r.jumlah_kebutuhan ?? ''));
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
        <select name="bahan_baku_id[]" onchange="updateSatuanLabel(this)" ${isViewMode ? 'disabled' : ''} class="flex-1 px-3 py-2 text-xs border border-gray-200 rounded-2xl bg-white outline-none focus:ring-1 focus:ring-gray-400">${opts}</select>
        <input type="number" name="jumlah_kebutuhan[]" step="0.01" min="0.01" value="${qty}" ${isViewMode ? 'disabled' : ''} placeholder="Qty" class="w-20 px-3 py-2 text-xs border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-gray-400 font-medium text-center">
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
