{{-- 
    Halaman: Tambah Menu
    Deskripsi: Form untuk menambahkan menu baru.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Tambah Menu" 
            :breadcrumbs="['Menu', 'Daftar Menu', 'Tambah']">
            <x-slot:actions>
                <x-ui.button href="{{ route('menu.index') }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Foto Menu --}}
                    <div class="md:col-span-2 flex items-start gap-6">
                        <div class="w-32 h-32 rounded-2xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 text-gray-400 overflow-hidden relative group">
                            <img id="previewFoto" src="" class="w-full h-full object-cover hidden">
                            <div id="placeholderFoto" class="flex flex-col items-center justify-center">
                                <x-heroicon-o-camera class="text-3xl mb-2 w-[1em] h-[1em] inline-block shrink-0" />
                                <span class="text-xs font-medium">Upload</span>
                            </div>
                            <input type="file" name="foto" id="foto" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewImage(this)">
                        </div>
                        <div class="flex-1 mt-2">
                            <h3 class="text-sm font-bold text-gray-900 mb-1">Foto Menu (Opsional)</h3>
                            <p class="text-xs text-gray-500 mb-3">Format JPG, PNG, atau WEBP. Maksimal 2MB. Resolusi disarankan 1:1 (Persegi).</p>
                            <button type="button" onclick="document.getElementById('foto').click()" class="text-xs font-medium bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition-colors">
                                Pilih File
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-2 border-t border-gray-100 pt-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Informasi Dasar</h3>
                    </div>

                    {{-- Nama Menu --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_menu" value="{{ old('nama_menu') }}" required placeholder="Contoh: Ayam Bakar Madu" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none transition-all">
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Menu <span class="text-red-500">*</span></label>
                        <select name="kategori_menu_id" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" {{ old('kategori_menu_id') == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama_kategori_kategori ?? $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis Menu --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Menu <span class="text-red-500">*</span></label>
                        <select name="jenis_menu_id" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                            <option value="1" {{ old('jenis_menu_id') == '1' ? 'selected' : '' }}>Dine-in (Makan di tempat)</option>
                            <option value="2" {{ old('jenis_menu_id') == '2' ? 'selected' : '' }}>Catering</option>
                            <option value="3" {{ old('jenis_menu_id') == '3' ? 'selected' : '' }}>Nasi Box</option>
                        </select>
                    </div>

                    {{-- Harga --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="harga_jual" value="{{ old('harga_jual') }}" required min="0" step="1" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none transition-all">
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Ketersediaan <span class="text-red-500">*</span></label>
                        <select name="status_aktif" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                            <option value="1" {{ (old('status_aktif', isset($menu) ? $menu->status_aktif : 1) == 1) ? 'selected' : '' }}>Tersedia</option>
                            <option value="0" {{ (old('status_aktif', isset($menu) ? $menu->status_aktif : 1) == 0) ? 'selected' : '' }}>Habis</option>
                        </select>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Keterangan Tambahan</label>
                        <textarea name="deskripsi" rows="3" placeholder="Opsional" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none resize-none transition-all">{{ old('deskripsi') }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <button type="reset" class="px-5 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors">
                        Reset Form
                    </button>
                    <x-ui.button type="submit" icon="document-arrow-down">Simpan Menu</x-ui.button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function previewImage(input) {
        const preview = document.getElementById('previewFoto');
        const placeholder = document.getElementById('placeholderFoto');
        
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = "";
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    }
</script>
@endsection
