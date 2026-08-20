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
            :breadcrumbs="['Manajemen Menu', 'Data Menu', 'Edit']">
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
                
                <div x-data="{ activeTab: 'informasi' }">
                    {{-- Tab Headers --}}
                    <div class="flex border-b border-gray-100 mb-6">
                        <button type="button" @click="activeTab = 'informasi'" 
                                :class="activeTab === 'informasi' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-6 py-4 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none">
                            Informasi Menu
                        </button>
                        <button type="button" @click="activeTab = 'resep'" 
                                :class="activeTab === 'resep' ? 'border-primary text-primary' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-6 py-4 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none">
                            Resep Bahan Baku
                        </button>
                    </div>

                    {{-- Tab 1: Informasi Menu --}}
                    <div x-show="activeTab === 'informasi'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

                        {{-- Nama Menu --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Menu <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_menu" value="{{ old('nama_menu', $menu->nama_menu) }}" required class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none transition-all">
                        </div>

                        {{-- Kategori --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori Menu <span class="text-red-500">*</span></label>
                            @php
                                $katOptionsEdit = [];
                                foreach($kategoris as $k) {
                                    $katOptionsEdit[$k->id] = $k->nama_kategori_kategori ?? $k->nama_kategori;
                                }
                            @endphp
                            <x-ui.searchable-select name="kategori_menu_id" :options="$katOptionsEdit" :selected="old('kategori_menu_id', $menu->kategori_menu_id)" placeholder="-- Pilih Kategori --" required="true" />
                        </div>

                        {{-- Jenis Menu --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Menu <span class="text-red-500">*</span></label>
                            @php
                                $jenisOptionsEdit = [];
                                foreach($jenis_menu as $j) {
                                    $jenisOptionsEdit[$j->id] = $j->nama_jenis;
                                }
                            @endphp
                            <x-ui.searchable-select name="jenis_menu_id" :options="$jenisOptionsEdit" :selected="old('jenis_menu_id', $menu->jenis_menu_id)" placeholder="-- Pilih Jenis Menu --" required="true" />
                        </div>

                        {{-- Harga --}}
                        <div>
                            <x-ui.input-currency label="Harga (Rp)" name="harga_jual" value="{{ old('harga_jual', $menu->harga_jual) }}" required="true" />
                        </div>

                        {{-- Minimal Pemesanan --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Minimal Pemesanan</label>
                            <input type="number" name="minimal_pemesanan" value="{{ old('minimal_pemesanan', $menu->minimal_pemesanan) }}" placeholder="Contoh: 20" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none transition-all">
                            <p class="text-xs text-gray-400 mt-1">Hanya untuk Katering & Nasi Box</p>
                        </div>

                        {{-- Status --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Ketersediaan <span class="text-red-500">*</span></label>
                            <x-ui.searchable-select name="status_aktif" :options="['tersedia' => 'Tersedia', 'habis' => 'Habis']" :selected="old('status_aktif', $menu->status_aktif ? 'tersedia' : 'habis')" placeholder="-- Pilih Status --" required="true" />
                        </div>

                        {{-- Deskripsi --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi / Keterangan Tambahan</label>
                            <textarea name="deskripsi" rows="3" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none resize-none transition-all">{{ old('deskripsi', $menu->deskripsi) }}</textarea>
                        </div>
                    </div>

                    {{-- Tab 2: Komposisi Bahan Baku / Resep --}}
                    <div x-show="activeTab === 'resep'" style="display: none;">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Komposisi Bahan Baku (Resep Takaran)</h3>
                                <p class="text-sm text-gray-500 mt-1">Masukkan bahan baku yang dibutuhkan untuk 1 porsi menu ini.</p>
                            </div>
                            <button type="button" onclick="addBahanBakuRow()" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold bg-primary text-white rounded-xl hover:bg-primary/90 transition-colors">
                                <x-heroicon-o-plus class="w-5 h-5 inline-block" /> Tambah Bahan
                            </button>
                        </div>
                        <div class="space-y-3" id="bahanBakuContainer">
                            @forelse($menu->resep_menu as $resep)
                                <div class="flex gap-3 items-start bahan-baku-row">
                                    <div class="flex-1">
                                        <select name="bahan_baku_id[]" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none bg-white transition-all">
                                            <option value="">Pilih Bahan Baku</option>
                                            @foreach($bahanBakus as $bb)
                                                <option value="{{ $bb->id }}" {{ $resep->bahan_baku_id == $bb->id ? 'selected' : '' }}>{{ $bb->nama_bahan }} ({{ $bb->satuan->nama_satuan ?? '-' }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-32">
                                        <input type="number" step="0.01" name="jumlah_kebutuhan[]" value="{{ (float)($resep->jumlah_kebutuhan ?? $resep->jumlah) }}" placeholder="Takaran" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none transition-all">
                                    </div>
                                    <div>
                                        <button type="button" onclick="this.closest('.bahan-baku-row').remove()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors mt-0">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="flex gap-3 items-start bahan-baku-row">
                                    <div class="flex-1">
                                        <select name="bahan_baku_id[]" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none bg-white transition-all">
                                            <option value="">Pilih Bahan Baku</option>
                                            @foreach($bahanBakus as $bb)
                                                <option value="{{ $bb->id }}">{{ $bb->nama_bahan }} ({{ $bb->satuan->nama_satuan ?? '-' }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="w-32">
                                        <input type="number" step="0.01" name="jumlah_kebutuhan[]" placeholder="Takaran" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none transition-all">
                                    </div>
                                    <div>
                                        <button type="button" onclick="this.closest('.bahan-baku-row').remove()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors mt-0">
                                            <x-heroicon-o-trash class="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                        </div>
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

    function addBahanBakuRow() {
        const container = document.getElementById('bahanBakuContainer');
        container.insertAdjacentHTML('beforeend', `
            <div class="flex gap-3 items-start bahan-baku-row">
                <div class="flex-1">
                    <select name="bahan_baku_id[]" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none bg-white transition-all">
                        <option value="">Pilih Bahan Baku</option>
                        @foreach($bahanBakus as $bb)
                            <option value="{{ $bb->id }}">{{ $bb->nama_bahan }} ({{ $bb->satuan->nama_satuan ?? '-' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-32">
                    <input type="number" step="0.01" name="jumlah_kebutuhan[]" placeholder="Takaran" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary/15 focus:border-primary outline-none transition-all">
                </div>
                <div>
                    <button type="button" onclick="this.closest('.bahan-baku-row').remove()" class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors mt-0">
                        <x-heroicon-o-trash class="w-5 h-5" />
                    </button>
                </div>
            </div>
        `);
    }
</script>
@endsection
