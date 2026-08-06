{{-- 
    Halaman: Detail Paket Katering / Nasi Box (Minimalist Edition)
--}}
@extends('layouts.pos')

@section('title', 'Detail Paket: ' . $paketCatering->nama_menu)

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Detail Paket: {{ $paketCatering->nama_menu }}" 
            subtitle="Rincian harga, status website, dan struktur komponen menu"
            :breadcrumbs="['Manajemen Menu', 'Paket', 'Detail']">
            <x-slot:actions>
                <x-ui.button href="{{ route('paket-catering.index', ['jenis' => $paketCatering->jenis_menu_id == 3 ? 'nasi_box' : 'catering']) }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
                <x-ui.button href="{{ route('paket-catering.edit', $paketCatering->id) }}" icon="pencil">Edit Paket</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            {{-- Info Ringkas Paket (Kolom Kiri) --}}
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-2xs p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis Paket:</span>
                        @if($paketCatering->jenis_menu_id == 3)
                            <span class="bg-purple-100 text-purple-900 border border-purple-200 px-3 py-1 rounded-full text-xs font-extrabold">Nasi Box</span>
                        @else
                            <span class="bg-blue-100 text-blue-900 border border-blue-200 px-3 py-1 rounded-full text-xs font-extrabold">Katering</span>
                        @endif
                    </div>

                    <div>
                        <h2 class="text-lg font-black text-gray-900 leading-tight">{{ $paketCatering->nama_menu }}</h2>
                        <p class="text-sm text-gray-500 font-medium mt-1">{{ $paketCatering->deskripsi ?: 'Tidak ada deskripsi' }}</p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 space-y-1">
                        <span class="text-xs font-extrabold uppercase text-gray-400 tracking-wider">Harga per Porsi / Box</span>
                        <div class="text-xl font-black text-[#0D3024]">Rp {{ number_format($paketCatering->harga_jual, 0, ',', '.') }}</div>
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <span class="text-xs font-bold text-gray-600">Status Website</span>
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full {{ $paketCatering->status_aktif ? 'bg-emerald-500' : 'bg-gray-300' }}"></span>
                            <span class="text-xs font-extrabold {{ $paketCatering->status_aktif ? 'text-emerald-900' : 'text-gray-500' }}">
                                {{ $paketCatering->status_aktif ? 'Tampil di Website' : 'Disembunyikan' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Struktur Komponen Menu (Kolom Kanan - Match Mockup) --}}
            <div class="lg:col-span-8">
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-2xs p-5 space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-extrabold text-gray-900">Struktur Item Menu</h3>
                            <p class="text-xs text-gray-500">Susunan menu yang didapatkan atau dipilih pemesan ({{ $paketCatering->komponen_paket->count() }} Item Menu)</p>
                        </div>
                    </div>

                    <div class="space-y-3.5">
                        @forelse($paketCatering->komponen_paket as $komponen)
                            <div class="bg-gray-50/80 border border-gray-200/90 rounded-xl p-4 space-y-3 shadow-2xs">
                                
                                {{-- Judul Komponen & Badge Tipe --}}
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-extrabold text-gray-900">{{ $komponen->nama_komponen }}</h4>
                                        @if($komponen->tipe_komponen == 'pilihan')
                                            <span class="text-amber-800 font-extrabold text-xs bg-amber-100/80 border border-amber-200 px-2.5 py-0.5 rounded-full">(pilih 1)</span>
                                        @else
                                            <span class="text-emerald-800 font-extrabold text-xs bg-emerald-100/80 border border-emerald-200 px-2.5 py-0.5 rounded-full">(pasti dapat)</span>
                                        @endif
                                    </div>
                                    <span class="text-xs font-mono font-bold text-gray-400">Urutan #{{ $komponen->urutan }}</span>
                                </div>

                                {{-- Pills Opsi Menu (Exact Match Mockup) --}}
                                <div class="flex flex-wrap gap-2 pt-1">
                                    @forelse($komponen->opsi as $opsi)
                                        <div class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full border border-gray-200 bg-white shadow-2xs transition-all hover:bg-gray-50">
                                            <span class="text-xs font-bold text-gray-800">{{ $opsi->nama_pilihan }}</span>
                                            @if($komponen->tipe_komponen == 'tetap')
                                                <span class="text-emerald-700 font-extrabold text-xs">✓</span>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">Belum ada pilihan menu di komponen ini.</span>
                                    @endforelse
                                </div>

                            </div>
                        @empty
                            <div class="text-center py-10 bg-gray-50 rounded-xl border border-dashed border-gray-200">
                                <x-heroicon-o-cube class="w-8 h-8 text-gray-300 mx-auto mb-1" />
                                <p class="text-xs font-bold text-gray-600">Belum ada komponen menu yang ditambahkan</p>
                            </div>
                        @endforelse
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>
@endsection
