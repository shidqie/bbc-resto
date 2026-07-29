{{-- 
    Halaman: Kategori Menu
    Deskripsi: Mengelola daftar kategori menu restoran, catering, dan nasi box.
--}}
@extends('layouts.pos')

@section('title', 'Kelola Kategori')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">
        
        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Kategori Menu</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola daftar kategori menu Resto, Catering, dan Nasi Box</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="document.getElementById('modalTambah').classList.remove('hidden')" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gray-900 rounded-lg px-3 py-2 hover:bg-gray-800 transition-colors">
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
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode kategori…" class="w-full pl-9 pr-3 py-2 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-gray-400 focus:border-gray-400 outline-none bg-white">
                    <input type="hidden" name="jenis" value="{{ request('jenis') }}">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
            
            <div class="flex items-center gap-1 text-xs font-medium overflow-x-auto no-scrollbar shrink-0">
                <span class="text-gray-500 mr-1">Filter Jenis:</span>
                <a href="{{ route('kategori-menu.index') }}" class="px-3 py-1.5 rounded-lg transition-colors {{ !request('jenis') ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Semua</a>
                <a href="{{ route('kategori-menu.index', ['jenis' => 'dine_in']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis') === 'dine_in' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Resto</a>
                <a href="{{ route('kategori-menu.index', ['jenis' => 'catering']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis') === 'catering' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Catering</a>
                <a href="{{ route('kategori-menu.index', ['jenis' => 'nasi_box']) }}" class="px-3 py-1.5 rounded-lg transition-colors {{ request('jenis') === 'nasi_box' ? 'bg-gray-900 text-white' : 'text-gray-600 hover:bg-gray-100' }}">Nasi Box</a>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden overflow-x-auto">
            <table class="w-full text-sm min-w-[700px]">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Kategori</th>
                        <th class="px-4 py-3 text-left">Jenis Menu</th>
                        <th class="px-4 py-3 text-left">Jumlah Menu</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($kategoris as $index => $kategori)
                        <tr class="hover:bg-gray-50/60 transition-colors group align-middle">
                            <td class="px-4 py-3 text-xs text-gray-500 font-medium">
                                {{ $kategoris->firstItem() + $index }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs font-semibold text-gray-600">
                                    {{ $kategori->kode_kategori ?? ('KTG-' . str_pad($kategori->id, 2, '0', STR_PAD_LEFT)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900">
                                {{ $kategori->nama }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $jenisColor = ['catering' => 'bg-blue-50 text-blue-700', 'nasi_box' => 'bg-purple-50 text-purple-700', 'dine_in' => 'bg-emerald-50 text-emerald-700'];
                                    $jenisLabel = ['catering' => 'Catering', 'nasi_box' => 'Nasi Box', 'dine_in' => 'Resto'];
                                    $jColor = $jenisColor[$kategori->jenis_menu] ?? 'bg-gray-100 text-gray-600';
                                    $jLabel = $jenisLabel[$kategori->jenis_menu] ?? $kategori->jenis_menu;
                                @endphp
                                <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded-md {{ $jColor }}">{{ $jLabel }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-700 font-medium">{{ $kategori->menus_count ?? 0 }} menu</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                    <button onclick="editKategori({{ $kategori->id }}, '{{ addslashes($kategori->nama) }}', '{{ $kategori->jenis_menu ?? 'dine_in' }}')" 
                                            class="p-1.5 rounded-md text-gray-500 hover:bg-blue-50 hover:text-blue-600 transition-colors" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <form action="{{ route('kategori-menu.destroy', $kategori->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kategori {{ addslashes($kategori->nama) }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-md text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Hapus">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400">
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
            {{ $kategoris->links('vendor.pagination.tailwind') }}
        </div>
        @endif

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
                <button type="submit" class="px-5 py-2.5 text-xs font-extrabold text-white bg-[#0F2E23] hover:bg-[#0a1f17] rounded-xl transition-all shadow-xs cursor-pointer">
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
                <button type="submit" class="px-5 py-2.5 text-xs font-extrabold text-white bg-[#0F2E23] hover:bg-[#0a1f17] rounded-xl transition-all shadow-xs cursor-pointer">
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
