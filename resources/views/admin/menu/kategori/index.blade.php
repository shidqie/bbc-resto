{{-- 
    Halaman: Kategori Menu
    Deskripsi: Mengelola daftar kategori menu restoran, catering, dan nasi box.
--}}
@extends('layouts.pos')

@section('title', 'Kelola Kategori')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 pb-10">
    <div class="w-full p-6 space-y-5">
        
        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Kategori Menu" subtitle="Kelola daftar kategori menu Restoran" :breadcrumbs="['Manajemen Menu', 'Kategori Menu']">
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <button onclick="openModal('modalTambah', 'drawerTambahPanel')" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Kategori
                    </button>
                </div>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table with integrated toolbar --}}
        <x-ui.data-table :paginator="$kategoris">
            <x-slot:toolbar>
                <form action="{{ route('kategori-menu.index') }}" method="GET" class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-4 w-full">
                    <div class="w-full xl:max-w-sm shrink-0">
                        <x-search-input name="search" value="{{ request('search') }}" placeholder="Cari nama kategori…" width="w-full" />
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(request()->filled('search'))
                            <a href="{{ route('kategori-menu.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                        @endif
                    </div>
                </form>
            </x-slot:toolbar>

            <x-ui.table class="min-w-[700px]">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-12">No.</th>
                    <th class="px-4 py-3.5 text-left">Nama Kategori</th>
                    <th class="px-4 py-3.5 text-left">Deskripsi</th>
                    <th class="px-4 py-3.5 text-center w-32">Jumlah Menu</th>
                    <th class="px-4 py-3.5 text-center w-32">Aksi</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($kategoris as $index => $kategori)
                        <x-ui.table.row class="align-middle">
                            <td class="px-4 py-4 text-sm text-gray-500 font-medium">
                                {{ $kategoris->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-4 font-bold text-gray-900">
                                {{ $kategori->nama_kategori }}
                            </td>
                            <td class="px-4 py-4 text-gray-600">
                                {{ $kategori->deskripsi ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                <x-ui.badge color="primary" size="sm">{{ $kategori->menu_count ?? 0 }} menu</x-ui.badge>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <x-ui.action-button onclick="editKategori({{ $kategori->id }}, '{{ addslashes($kategori->nama_kategori) }}', '{{ addslashes($kategori->deskripsi) }}')" title="Ubah">
                                        <x-heroicon-o-pencil-square class="w-4 h-4" />
                                    </x-ui.action-button>
                                    <form id="delete-kategori-{{ $kategori->id }}" action="{{ route('kategori-menu.destroy', $kategori->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <x-ui.action-button type="button" onclick="window.confirmDialog({ title: 'Hapus Kategori', name: '{{ addslashes($kategori->nama_kategori) }}', message: 'Data yang dihapus tidak dapat dikembalikan.', formId: 'delete-kategori-{{ $kategori->id }}', confirmText: 'Hapus', cancelText: 'Batal' })" title="Hapus">
                                            <x-heroicon-o-trash class="w-4 h-4" />
                                        </x-ui.action-button>
                                    </form>
                                </div>
                            </td>
                        </x-ui.table.row>
                    @empty
                    <x-empty-state icon="archive-box" title="Belum ada data kategori menu" message="Tambahkan kategori menu untuk mulai mengelompokkan menu." :colspan="5" />
                    @endforelse
                </tbody>
            </x-ui.table>
        </x-ui.data-table>

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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" placeholder="Contoh: Makanan Utama, Paket Hemat, dll" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kategori" id="editNama" required class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Deskripsi</label>
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
