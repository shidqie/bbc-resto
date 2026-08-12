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
                    <x-ui.button variant="primary" icon="plus" onclick="openModal('modalTambah', 'drawerTambahPanel')">
                        Tambah Kategori
                    </x-ui.button>
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
                            <x-ui.button href="{{ route('kategori-menu.index') }}" variant="danger" size="sm">Reset</x-ui.button>
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
                    <tr>
                        <td colspan="5">
                            <x-ui.empty-state icon="tag" title="Belum ada data kategori menu" message="Tambahkan kategori menu untuk mulai mengelompokkan menu." />
                        </td>
                    </tr>
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
                    <x-ui.input name="nama_kategori" label="Nama Kategori" placeholder="Contoh: Makanan Utama, Paket Hemat, dll" required />
                </div>

                <div>
                    <x-ui.textarea name="deskripsi" label="Deskripsi" placeholder="Tuliskan deskripsi kategori..." rows="3" />
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <x-ui.button variant="secondary" onclick="closeModal('modalTambah', 'drawerTambahPanel')">
                    Batal
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    Simpan
                </x-ui.button>
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
                    <x-ui.input name="nama_kategori" id="editNama" label="Nama Kategori" required />
                </div>

                <div>
                    <x-ui.textarea name="deskripsi" id="editDeskripsi" label="Deskripsi" placeholder="Tuliskan deskripsi kategori..." rows="3" />
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2 bg-gray-50/80 shrink-0 mt-auto">
                <x-ui.button variant="secondary" onclick="closeModal('modalEdit', 'drawerEditPanel')">
                    Batal
                </x-ui.button>
                <x-ui.button type="submit" variant="primary">
                    Simpan Perubahan
                </x-ui.button>
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
