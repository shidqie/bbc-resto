{{-- 
    Halaman: Kategori Menu
    Deskripsi: Mengelola daftar kategori untuk mengelompokkan menu.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Kategori Menu" 
            subtitle="Kelola daftar kategori untuk mengelompokkan menu."
            :breadcrumbs="['Menu', 'Kategori Menu']">
            <x-slot:actions>
                <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-[#3B82F6] hover:bg-[#2563EB] text-white px-5 py-2.5 rounded-xl font-medium text-sm transition-colors shadow-sm">
                    <i class="fas fa-plus"></i> Tambah Kategori
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table --}}
        <x-ui.data-table :paginator="$kategoris">
            <x-slot:toolbar>
                <div class="relative w-full sm:w-72">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <form action="{{ route('kategori-menu.index') }}" method="GET">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kategori..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                    </form>
                </div>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Nama Kategori</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Jumlah Menu</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($kategoris as $kategori)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $kategori->nama_kategori ?? $kategori->nama }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 px-3 py-1 rounded-full text-xs font-medium">{{ $kategori->menus_count ?? 0 }} menu</span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button onclick="editKategori({{ $kategori->id }}, '{{ addslashes($kategori->nama_kategori ?? $kategori->nama) }}')" class="p-2 text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('kategori-menu.destroy', $kategori->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <x-ui.empty-state icon="fa-folder-open" title="Belum ada kategori menu." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>

{{-- Modal Tambah --}}
<div id="modalTambah" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="document.getElementById('modalTambah').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-xl w-[90%] max-w-md p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tambah Kategori Menu</h3>
        <form action="{{ route('kategori-menu.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" name="nama" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none">
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-white bg-[#3B82F6] hover:bg-[#2563EB] rounded-xl font-medium transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modalEdit" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="document.getElementById('modalEdit').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl shadow-xl w-[90%] max-w-md p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Edit Kategori Menu</h3>
        <form id="formEdit" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Kategori</label>
                <input type="text" name="nama" id="editNama" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none">
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-white bg-[#3B82F6] hover:bg-[#2563EB] rounded-xl font-medium transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editKategori(id, nama) {
        document.getElementById('formEdit').action = `/kategori-menu/${id}`;
        document.getElementById('editNama').value = nama;
        document.getElementById('modalEdit').classList.remove('hidden');
    }
</script>
@endsection
