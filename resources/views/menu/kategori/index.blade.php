{{-- 
    Halaman: Kategori Menu
    Deskripsi: Mengelola daftar kategori menu restoran, catering, dan nasi box.
--}}
@extends('layouts.pos')

@section('title', 'Kelola Kategori')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">
        
        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Kategori Menu</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Kelola daftar kategori menu Restoran</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="openModal('modalTambah', 'drawerTambahPanel')" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Kategori
                </button>
            </div>
        </div>

        <x-ui.alert />

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('kategori-menu.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama kategori…" class="w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Nama Kategori</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-center w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($kategoris as $index => $kategori)
                        <tr class="hover:bg-gray-50/60 transition-colors group align-middle">
                            <td class="px-4 py-3 text-xs text-gray-500 font-medium">
                                {{ $kategoris->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900">
                                {{ $kategori->nama_kategori }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $kategori->deskripsi ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button onclick="editKategori({{ $kategori->id }}, '{{ addslashes($kategori->nama_kategori) }}', '{{ addslashes($kategori->deskripsi) }}')" 
                                            title="Ubah" class="w-7 h-7 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors">
                                        <x-heroicon-o-pencil-square class="w-3 h-3" />
                                    </button>
                                    <form action="{{ route('kategori-menu.destroy', $kategori->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori {{ addslashes($kategori->nama_kategori) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="w-7 h-7 rounded-lg flex items-center justify-center bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                                            <x-heroicon-o-trash class="w-3 h-3" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p class="text-sm font-medium text-gray-500">Belum ada data kategori menu.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($kategoris->hasPages())
        <div class="mt-4">
            {{ $kategoris->links() }}
        </div>
        @endif

    </div>
</div>

{{-- Drawer Tambah Kategori --}}
<div id="modalTambah" class="fixed inset-x-0 bottom-0 top-16 z-50 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeModal('modalTambah', 'drawerTambahPanel')"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerTambahPanel">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <div>
                <h3 class="font-semibold text-gray-900">Tambah Kategori Baru</h3>
                <p class="text-xs text-gray-400 mt-0.5">Tambah kategori menu resto, catering, atau nasi box</p>
            </div>
            <button onclick="closeModal('modalTambah', 'drawerTambahPanel')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        
        <form action="{{ route('kategori-menu.store') }}" method="POST" class="flex-1 overflow-y-auto flex flex-col justify-between">
            @csrf
            <div class="px-5 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" placeholder="Contoh: Makanan Utama, Paket Hemat, dll" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" placeholder="Tuliskan deskripsi kategori..." rows="3" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all"></textarea>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <button type="button" onclick="closeModal('modalTambah', 'drawerTambahPanel')" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-5 py-2 hover:bg-gray-800 transition-colors">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Drawer Edit Kategori --}}
<div id="modalEdit" class="fixed inset-x-0 bottom-0 top-16 z-50 hidden">
    <div class="absolute inset-0 bg-black/30 backdrop-blur-[2px]" onclick="closeModal('modalEdit', 'drawerEditPanel')"></div>
    <div class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl flex flex-col translate-x-full transition-transform duration-300" id="drawerEditPanel">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
            <div>
                <h3 class="font-semibold text-gray-900">Edit Kategori Menu</h3>
                <p class="text-xs text-gray-400 mt-0.5">Perbarui nama atau deskripsi kategori menu</p>
            </div>
            <button onclick="closeModal('modalEdit', 'drawerEditPanel')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form id="formEdit" method="POST" class="flex-1 overflow-y-auto flex flex-col justify-between">
            @csrf
            @method('PUT')
            <div class="px-5 py-5 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" id="editNama" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">Deskripsi</label>
                    <textarea name="deskripsi" id="editDeskripsi" placeholder="Tuliskan deskripsi kategori..." rows="3" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all"></textarea>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <button type="button" onclick="closeModal('modalEdit', 'drawerEditPanel')" class="text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-5 py-2 hover:bg-gray-800 transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal(modalId, panelId) {
        const modal = document.getElementById(modalId);
        const panel = document.getElementById(panelId);
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        requestAnimationFrame(() => {
            panel.classList.remove('translate-x-full');
        });
    }

    function closeModal(modalId, panelId) {
        const modal = document.getElementById(modalId);
        const panel = document.getElementById(panelId);
        panel.classList.add('translate-x-full');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.style.display = '';
        }, 300);
    }

    function editKategori(id, nama, deskripsi) {
        document.getElementById('formEdit').action = `/kategori-menu/${id}`;
        document.getElementById('editNama').value = nama;
        document.getElementById('editDeskripsi').value = deskripsi !== '-' ? deskripsi : '';
        openModal('modalEdit', 'drawerEditPanel');
    }
</script>
@endsection
