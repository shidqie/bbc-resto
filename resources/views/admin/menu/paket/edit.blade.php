{{-- 
    Halaman: Edit Paket Katering / Nasi Box (Minimalist Edition - Exact Mockup Style)
--}}
@extends('layouts.pos')

@section('title', 'Edit Paket: ' . $paketCatering->nama_menu)

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Edit Paket: {{ $paketCatering->nama_menu }}" 
            subtitle="Ubah informasi harga, susunan komponen, dan pilihan menu paket"
            :breadcrumbs="['Manajemen Menu', 'Paket', 'Edit']">
            <x-slot:actions>
                <x-ui.button href="{{ route('paket-catering.index', ['jenis' => $jenis]) }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="bg-white rounded-xl border border-gray-200/80 shadow-2xs p-6 space-y-6">
            <form action="{{ route('paket-catering.update', $paketCatering->id) }}" method="POST" enctype="multipart/form-data" id="paketForm" class="space-y-6">
                <x-form-error />
                @csrf
                @method('PUT')

                {{-- Informasi Utama Paket --}}
                <div class="space-y-4">
                    <h3 class="text-sm font-extrabold text-gray-900 border-b border-gray-100 pb-2">Informasi Utama Paket</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-1">Nama Paket <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_paket" required value="{{ old('nama_paket', $paketCatering->nama_menu) }}" 
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0D3024]/10 focus:border-[#0D3024] outline-none text-xs font-extrabold text-gray-900">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-1">Jenis Paket <span class="text-red-500">*</span></label>
                            <select name="jenis_paket" required class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0D3024]/10 focus:border-[#0D3024] outline-none text-sm font-bold text-gray-900">
                                <option value="catering" {{ old('jenis_paket', $jenis) == 'catering' ? 'selected' : '' }}>Katering</option>
                                <option value="nasi_box" {{ old('jenis_paket', $jenis) == 'nasi_box' ? 'selected' : '' }}>Nasi Box</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-1">Harga / Porsi (Rp) <span class="text-red-500">*</span></label>
                            <input type="number" name="harga" required min="0" value="{{ old('harga', $paketCatering->harga_jual) }}" 
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0D3024]/10 focus:border-[#0D3024] outline-none text-xs font-black text-[#0D3024]">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-1">Foto Paket</label>
                            <input type="file" name="foto" accept="image/*"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm font-medium text-gray-700">
                            @if($paketCatering->foto)
                                <p class="text-xs text-emerald-700 font-bold mt-1">✓ Foto saat ini terpasang</p>
                            @endif
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 uppercase tracking-wide mb-1">Deskripsi Singkat</label>
                            <input type="text" name="deskripsi" value="{{ old('deskripsi', $paketCatering->deskripsi) }}" placeholder="Contoh: Paket hemat prasmanan lengkap…" 
                                   class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0D3024]/10 focus:border-[#0D3024] outline-none text-xs font-medium">
                        </div>
                    </div>
                </div>

                {{-- Komponen Paket --}}
                <div class="space-y-4 pt-2">
                    @include('admin.menu.paket.partials.komponen-builder', ['existingKomponen' => $paketCatering->komponen_paket ?? []])
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                    <a href="{{ route('paket-catering.index', ['jenis' => $jenis]) }}" class="px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50 transition-all">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#0D3024] hover:bg-[#0a1f17] text-white text-sm font-extrabold transition-all shadow-sm active:scale-95 flex items-center gap-2">
                        <x-heroicon-o-check class="w-4 h-4" /> Simpan Perubahan Paket
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection
