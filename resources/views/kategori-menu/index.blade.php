{{-- 
    Halaman: Kategori Menu
    Deskripsi: Mengelola daftar kategori menu restoran, catering, dan nasi box.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Kategori Menu" 
            subtitle="Kelola daftar kategori menu Resto, Catering, dan Nasi Box"
            :breadcrumbs="['Menu', 'Kategori Menu']">
            <x-slot:actions>
                <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-[#0F2E23] hover:bg-[#0a1f17] text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-colors shadow-sm active:scale-95">
                    <x-heroicon-o-plus class="w-5 h-5 inline-block shrink-0" /> Tambah Kategori
                </button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        {{-- Table --}}
        <x-ui.data-table :paginator="$kategoris">
            <x-slot:toolbar>
                <div class="flex flex-col sm:flex-row gap-3 w-full justify-between items-center">
                    <div class="relative w-full sm:w-72">
                        <x-heroicon-o-magnifying-glass class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 w-4 h-4 inline-block shrink-0" />
                        <form action="{{ route('kategori-menu.index') }}" method="GET">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode kategori…" class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none bg-white transition-all">
                        </form>
                    </div>

                    {{-- Filter Jenis Menu --}}
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Jenis:</span>
                        <a href="{{ route('kategori-menu.index') }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ !request('jenis') ? 'bg-[#0F2E23] text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">Semua</a>
                        <a href="{{ route('kategori-menu.index', ['jenis' => 'dine_in']) }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('jenis') === 'dine_in' ? 'bg-emerald-800 text-white' : 'bg-emerald-50 text-emerald-900 border border-emerald-200' }}">Resto</a>
                        <a href="{{ route('kategori-menu.index', ['jenis' => 'catering']) }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('jenis') === 'catering' ? 'bg-blue-800 text-white' : 'bg-blue-50 text-blue-900 border border-blue-200' }}">Catering</a>
                        <a href="{{ route('kategori-menu.index', ['jenis' => 'nasi_box']) }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ request('jenis') === 'nasi_box' ? 'bg-purple-800 text-white' : 'bg-purple-50 text-purple-900 border border-purple-200' }}">Nasi Box</a>
                    </div>
                </div>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-5 py-4 font-bold text-center w-12">No</th>
                        <th class="px-5 py-4 font-bold">Kode Kategori</th>
                        <th class="px-5 py-4 font-bold">Nama Kategori</th>
                        <th class="px-5 py-4 font-bold">Jenis Menu</th>
                        <th class="px-5 py-4 font-bold">Jumlah Menu</th>
                        <th class="px-5 py-4 font-bold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($kategoris as $index => $kategori)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-5 py-4 text-center font-bold text-gray-500">
                                {{ $kategoris->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-[#0F2E23]">
                                {{ $kategori->kode_kategori ?? ('KTG-' . str_pad($kategori->id, 2, '0', STR_PAD_LEFT)) }}
                            </td>
                            <td class="px-5 py-4 font-bold text-gray-900">
                                {{ $kategori->nama }}
                            </td>
                            <td class="px-5 py-4">
                                @if($kategori->jenis_menu === 'catering')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Catering</span>
                                @elseif($kategori->jenis_menu === 'nasi_box')
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800 border border-purple-200">Nasi Box</span>
                                @else
                                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Resto</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-lg text-xs font-bold">{{ $kategori->menus_count ?? 0 }} menu</span>
                            </td>
                            <td class="px-5 py-4 text-right space-x-1.5">
                                <button onclick="editKategori({{ $kategori->id }}, '{{ addslashes($kategori->nama) }}', '{{ $kategori->jenis_menu ?? 'dine_in' }}')" 
                                        class="px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-xl border border-blue-200 transition-colors inline-flex items-center gap-1">
                                    <x-heroicon-o-pencil-square class="w-3.5 h-3.5" /> Edit
                                </button>
                                <form action="{{ route('kategori-menu.destroy', $kategori->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori {{ addslashes($kategori->nama) }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 text-xs font-bold text-red-700 bg-red-50 hover:bg-red-100 rounded-xl border border-red-200 transition-colors inline-flex items-center gap-1">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" /> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <x-ui.empty-state icon="fa-folder-open" title="Belum ada kategori menu." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>

{{-- Modal Tambah Kategori --}}
<div id="modalTambah" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="document.getElementById('modalTambah').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl w-[90%] max-w-md p-6 space-y-4 border border-gray-100">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-base font-extrabold text-[#0F2E23]">Tambah Kategori Baru</h3>
            <button onclick="document.getElementById('modalTambah').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold text-xl leading-none">&times;</button>
        </div>
        
        <form action="{{ route('kategori-menu.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="nama" placeholder="Contoh: Makanan Utama, Paket Hemat, dll" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Jenis Menu <span class="text-red-500">*</span></label>
                <select name="jenis_menu" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all font-semibold">
                    <option value="dine_in">Resto (Dine-In / Takeaway)</option>
                    <option value="catering">Catering</option>
                    <option value="nasi_box">Nasi Box</option>
                </select>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalTambah').classList.add('hidden')" class="px-4 py-2.5 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 text-xs font-extrabold text-white bg-[#0F2E23] hover:bg-[#0a1f17] rounded-xl transition-all shadow-xs">
                    Simpan Kategori
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Kategori --}}
<div id="modalEdit" class="fixed inset-0 z-[100] hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="document.getElementById('modalEdit').classList.add('hidden')"></div>
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-3xl shadow-2xl w-[90%] max-w-md p-6 space-y-4 border border-gray-100">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="text-base font-extrabold text-[#0F2E23]">Edit Kategori Menu</h3>
            <button onclick="document.getElementById('modalEdit').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 font-bold text-xl leading-none">&times;</button>
        </div>

        <form id="formEdit" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="nama" id="editNama" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wide mb-1">Jenis Menu <span class="text-red-500">*</span></label>
                <select name="jenis_menu" id="editJenisMenu" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] outline-none text-sm transition-all font-semibold">
                    <option value="dine_in">Resto (Dine-In / Takeaway)</option>
                    <option value="catering">Catering</option>
                    <option value="nasi_box">Nasi Box</option>
                </select>
            </div>

            <div class="flex justify-end gap-2.5 pt-3 border-t">
                <button type="button" onclick="document.getElementById('modalEdit').classList.add('hidden')" class="px-4 py-2.5 text-xs font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 text-xs font-extrabold text-white bg-[#0F2E23] hover:bg-[#0a1f17] rounded-xl transition-all shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function editKategori(id, nama, jenis) {
        document.getElementById('formEdit').action = `/kategori-menu/${id}`;
        document.getElementById('editNama').value = nama;
        document.getElementById('editJenisMenu').value = jenis || 'dine_in';
        document.getElementById('modalEdit').classList.remove('hidden');
    }
</script>
@endsection
