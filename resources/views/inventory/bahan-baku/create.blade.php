{{-- 
    Halaman: Tambah Bahan Baku
    Deskripsi: Form untuk menambahkan data bahan baku baru ke sistem.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Tambah Bahan Baku" 
            :breadcrumbs="['Bahan Baku', 'Daftar Bahan Baku', 'Tambah Data']">
            <x-slot:actions>
                <x-ui.button href="{{ route('bahan-baku.index') }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Form Card --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-lg font-semibold text-gray-900">Informasi Bahan Baku</h2>
                <p class="text-sm text-gray-500 font-medium mt-1">Lengkapi data bahan baku baru yang akan ditambahkan ke sistem.</p>
            </div>
            
            <form action="{{ route('bahan-baku.store') }}" method="POST" class="p-6">
                <x-form-error />
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Kolom Kiri --}}
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Bahan <span class="text-red-500">*</span></label>
                            <input type="text" name="kode_bahan" value="{{ old('kode_bahan', $kodeBahan) }}" readonly 
                                   class="bg-gray-100 border border-gray-200 text-gray-500 text-sm rounded-xl block w-full p-3 cursor-not-allowed">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bahan Baku <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_bahan" value="{{ old('nama_bahan') }}" required 
                                   class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-[#0D3024]/10 focus:border-[#0D3024] block w-full p-3 transition-colors outline-none placeholder-gray-400 font-medium" 
                                   placeholder="Contoh: Beras Premium">
                        </div>


                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-500">*</span></label>
                                <select name="kategori_bahan_baku_id" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] block w-full p-3 transition-colors outline-none">
                                    <option value="">Pilih Kategori</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_bahan_baku_id') == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama_kategori }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Satuan Dasar <span class="text-red-500">*</span></label>
                                <select name="satuan_id" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] block w-full p-3 transition-colors outline-none">
                                    <option value="">Pilih Satuan</option>
                                    @foreach($satuans as $satuan)
                                        <option value="{{ $satuan->id }}" {{ old('satuan_id') == $satuan->id ? 'selected' : '' }}>{{ $satuan->nama_satuan }} ({{ $satuan->singkatan }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Peruntukan <span class="text-red-500">*</span></label>
                            <select name="jenis_peruntukan" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] block w-full p-3 transition-colors outline-none">
                                <option value="Semua" {{ old('jenis_peruntukan') == 'Semua' ? 'selected' : '' }}>Semua (Bisa dipakai Reguler & Catering)</option>
                                <option value="Reguler" {{ old('jenis_peruntukan') == 'Reguler' ? 'selected' : '' }}>Khusus Reguler</option>
                                <option value="Catering" {{ old('jenis_peruntukan') == 'Catering' ? 'selected' : '' }}>Khusus Catering</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Data <span class="text-red-500">*</span></label>
                            <select name="status_aktif" required class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] block w-full p-3 transition-colors outline-none">
                                <option value="1" {{ old('status_aktif') == '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ old('status_aktif') == '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    
                    {{-- Kolom Kanan --}}
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" min="0" name="stok" value="{{ old('stok', 0) }}" required 
                                       class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] block w-full p-3 transition-colors outline-none">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Batas Minimum <span class="text-red-500">*</span></label>
                                <input type="number" step="0.01" min="0" name="stok_minimal" value="{{ old('stok_minimal', 0) }}" required 
                                       class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] block w-full p-3 transition-colors outline-none">
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Tombol Aksi --}}
                <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
                    <x-ui.button href="{{ route('bahan-baku.index') }}" variant="outline">Batal</x-ui.button>
                    <x-ui.button type="submit" icon="document-arrow-down">Simpan Data</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
