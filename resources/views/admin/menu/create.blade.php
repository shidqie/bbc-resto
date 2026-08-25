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
            :breadcrumbs="['Manajemen Menu', 'Data Menu', 'Tambah']">
            <x-slot:actions>
                <x-ui.button href="{{ route('menu.index') }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            <form action="{{ route('menu.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                <x-form-error />
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Foto Menu --}}
                    <div class="md:col-span-2 flex items-start gap-6">
                        <div class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50 text-gray-400 overflow-hidden relative group">
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
                            <button type="button" onclick="document.getElementById('foto').click()" class="text-sm font-medium bg-gray-100 text-gray-600 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition-colors">
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
                        <input type="text" name="nama_menu" value="{{ old('nama_menu') }}" required placeholder="Contoh: Ayam Bakar Madu" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none transition-all">
                    </div>

                    {{-- Kategori --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Menu <span class="text-red-500">*</span></label>
                        @php
                            $katOptions = [];
                            foreach($kategoris as $kategori) {
                                if(!isset($kategori->jenis_menu_id) || $kategori->jenis_menu_id == 1 || $kategori->jenis_menu_id == '') {
                                    $katOptions[$kategori->id] = $kategori->nama_kategori_kategori ?? $kategori->nama_kategori;
                                }
                            }
                        @endphp
                        <x-ui.searchable-select name="kategori_menu_id" :options="$katOptions" :selected="old('kategori_menu_id')" placeholder="-- Pilih Kategori --" required="true" />
                    </div>

                    {{-- Jenis Menu (Hidden, Force Dine In) --}}
                    <input type="hidden" name="jenis_menu_id" value="1">

                    {{-- Harga --}}
                    <div>
                        <x-ui.input-currency label="Harga (Rp)" name="harga_jual" value="{{ old('harga_jual') }}" required="true" />
                    </div>

                    {{-- Minimal Pemesanan --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Minimal Pemesanan</label>
                        <input type="number" name="minimal_pemesanan" value="{{ old('minimal_pemesanan') }}" placeholder="Contoh: 20" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none transition-all">
                        <p class="text-xs text-gray-400 mt-1">Hanya untuk Katering & Nasi Box</p>
                    </div>

                    {{-- Status --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status Ketersediaan <span class="text-red-500">*</span></label>
                        <x-ui.searchable-select name="status_aktif" :options="['1' => 'Tersedia', '0' => 'Habis']" :selected="old('status_aktif', 1)" placeholder="-- Pilih Status --" required="true" />
                    </div>

                    {{-- Deskripsi --}}
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Keterangan Tambahan</label>
                        <textarea name="deskripsi" rows="3" placeholder="Opsional" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none resize-none transition-all">{{ old('deskripsi') }}</textarea>
                    </div>

                    {{-- Komposisi Bahan Baku / Resep --}}
                    <div class="md:col-span-2 border-t border-gray-100 pt-6 mt-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-sm font-bold text-gray-900">Komposisi Bahan Baku (Resep Takaran)</h3>
                            <button type="button" onclick="addBahanBakuRow()" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-sm font-semibold bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors">
                                <x-heroicon-o-plus class="w-4 h-4 inline-block" /> Tambah Bahan
                            </button>
                        </div>
                        <div class="space-y-3" id="bahanBakuContainer">
                            <div class="flex gap-3 items-start bahan-baku-row">
                                <div class="flex-1">
                                    <div class="relative">
                                        <select name="bahan_baku_id[]" class="w-full appearance-none px-4 py-2.5 pr-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none bg-white text-sm font-semibold shadow-xs cursor-pointer transition-all">
                                            <option value="">Pilih Bahan Baku</option>
                                            @foreach($bahanBakus as $bb)
                                                <option value="{{ $bb->id }}">{{ $bb->nama_bahan }} ({{ $bb->satuan->nama_satuan ?? '-' }})</option>
                                            @endforeach
                                        </select>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400">
                                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                                        </span>
                                    </div>
                                </div>
                                <div class="w-32">
                                        <input type="number" step="0.01" name="jumlah_kebutuhan[]" placeholder="Takaran" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none text-sm font-medium transition-all shadow-xs">
                                </div>
                                <div>
                                    <button type="button" onclick="this.closest('.bahan-baku-row').remove()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors mt-0 cursor-pointer">
                                        <x-heroicon-o-trash class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-gray-100">
                    <button type="reset" class="px-5 py-2.5 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl font-medium transition-colors cursor-pointer">
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

    function addBahanBakuRow() {
        const container = document.getElementById('bahanBakuContainer');
        container.insertAdjacentHTML('beforeend', `
            <div class="flex gap-3 items-start bahan-baku-row">
                <div class="flex-1">
                    <div class="relative">
                        <select name="bahan_baku_id[]" class="w-full appearance-none px-4 py-2.5 pr-10 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none bg-white text-sm font-semibold shadow-xs cursor-pointer transition-all">
                            <option value="">Pilih Bahan Baku</option>
                            @foreach($bahanBakus as $bb)
                                <option value="{{ $bb->id }}">{{ $bb->nama_bahan }} ({{ $bb->satuan->nama_satuan ?? '-' }})</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </span>
                    </div>
                </div>
                <div class="w-32">
                    <input type="number" step="0.01" name="jumlah_kebutuhan[]" placeholder="Takaran" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none text-sm font-medium transition-all shadow-xs">
                </div>
                <div>
                    <button type="button" onclick="this.closest('.bahan-baku-row').remove()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors mt-0 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </div>
            </div>
        `);
    }
</script>
@endsection
