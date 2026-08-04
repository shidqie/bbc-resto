{{-- 
    Halaman: Edit Menu
    Deskripsi: Form untuk mengedit data menu yang ada.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Edit Menu: {{ $menu->nama_menu }}" 
            :breadcrumbs="['Menu', 'Daftar Menu', 'Edit']">
            <x-slot:actions>
                <x-ui.button href="{{ route('menu.index') }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <form action="{{ route('menu.update', $menu->id) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <x-form-error />
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Foto Menu --}}
                    <div class="md:col-span-2 flex items-start gap-6">
                        <div class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 text-gray-400 overflow-hidden relative group">
                            <img id="previewFoto" src="{{ $menu->foto ? Storage::url($menu->foto) : '' }}" class="w-full h-full object-cover {{ $menu->foto ? '' : 'hidden' }}">
                            <div id="placeholderFoto" class="flex flex-col items-center justify-center {{ $menu->foto ? 'hidden' : '' }}">
                                <x-heroicon-o-camera class="text-3xl mb-2 w-[1em] h-[1em] inline-block shrink-0" />
                                <span class="text-xs font-medium">Upload</span>
                            </div>
                            <input type="file" name="foto" id="foto" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" onchange="previewImage(this)">
                        </div>
                        <div class="flex-1 mt-2">
                            <h3 class="text-sm font-bold text-gray-900 mb-1">Foto Menu</h3>
                            <p class="text-xs text-gray-500 mb-3">Biarkan kosong jika tidak ingin mengubah foto. Format JPG, PNG, atau WEBP. Maks 2MB.</p>
                            <button type="button" onclick="document.getElementById('foto').click()" class="text-sm font-medium bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition-colors">
                                Ganti File
                            </button>
                        </div>
                    </div>

                    <div class="md:col-span-2 border-t border-gray-100 pt-6">
                        <h3 class="text-sm font-bold text-gray-900 mb-4">Informasi Dasar</h3>
                    </div>

                    {{-- Nama Menu --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_menu" value="{{ old('nama_menu', $menu->nama_menu) }}" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none transition-all">
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Menu <span class="text-red-500">*</span></label>
                        <select name="kategori_menu_id" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" {{ old('kategori_menu_id', $menu->kategori_menu_id) == $kategori->id ? 'selected' : '' }}>{{ $kategori->nama_kategori_kategori ?? $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jenis Menu --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Menu <span class="text-red-500">*</span></label>
                        <select name="jenis_menu_id" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                            @foreach($jenis_menu as $jenis)
                                <option value="{{ $jenis->id }}" {{ old('jenis_menu_id', $menu->jenis_menu_id) == $jenis->id ? 'selected' : '' }}>{{ $jenis->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Harga --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="harga_jual" value="{{ old('harga_jual', $menu->harga_jual) }}" required min="0" step="1" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none transition-all">
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Ketersediaan <span class="text-red-500">*</span></label>
                        <select name="status_aktif" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                            <option value="tersedia" {{ old('status_aktif', $menu->status_aktif ? 'tersedia' : 'habis') === 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="habis" {{ old('status_aktif', $menu->status_aktif ? 'tersedia' : 'habis') === 'habis' ? 'selected' : '' }}>Habis</option>
                        </select>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Keterangan Tambahan</label>
                        <textarea name="deskripsi" rows="3" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none resize-none transition-all">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <x-ui.button href="{{ route('menu.index') }}" variant="outline">Batal</x-ui.button>
                    <x-ui.button type="submit" icon="document-arrow-down">Simpan Perubahan</x-ui.button>
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
            // Revert back if no file chosen but previously there's a file
            @if($menu->foto)
                preview.src = "{{ Storage::url($menu->foto) }}";
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
            @else
                preview.src = "";
                preview.classList.add('hidden');
                placeholder.classList.remove('hidden');
            @endif
        }
    }
</script>
@endsection
