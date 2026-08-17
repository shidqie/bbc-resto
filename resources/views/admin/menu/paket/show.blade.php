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

        <div x-data="{ tab: 'informasi' }">
            {{-- Tab Navigation --}}
            <div class="flex gap-4 border-b border-gray-200 mb-6">
                <button @click="tab = 'informasi'" :class="tab == 'informasi' ? 'border-[#0D3024] text-[#0D3024]' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 border-b-2 font-bold text-sm transition-colors">Informasi Paket</button>
                <button @click="tab = 'menu'" :class="tab == 'menu' ? 'border-[#0D3024] text-[#0D3024]' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 border-b-2 font-bold text-sm transition-colors">Daftar Menu Paket</button>
                <button @click="tab = 'bahan'" :class="tab == 'bahan' ? 'border-[#0D3024] text-[#0D3024]' : 'border-transparent text-gray-500 hover:text-gray-700'" class="px-4 py-3 border-b-2 font-bold text-sm transition-colors">Kebutuhan Bahan Baku</button>
            </div>

            {{-- TAB 1: Informasi Paket --}}
            <div x-show="tab === 'informasi'" x-cloak>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-1 space-y-4">
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
                </div>
            </div>

            {{-- TAB 2: Daftar Menu Paket --}}
            <div x-show="tab === 'menu'" x-cloak>
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
                                        <h4 class="text-sm font-extrabold text-gray-900">{{ $komponen->nama_komponen }} <span class="text-xs text-gray-500 font-medium ml-1">({{ (float)($komponen->jumlah ?? 1) }} porsi)</span></h4>
                                        @if($komponen->tipe_komponen == 'pilihan')
                                            <span class="text-amber-800 font-extrabold text-xs bg-amber-100/80 border border-amber-200 px-2.5 py-0.5 rounded-full">(pilih 1)</span>
                                        @else
                                            <span class="text-emerald-800 font-extrabold text-xs bg-emerald-100/80 border border-emerald-200 px-2.5 py-0.5 rounded-full">(pasti dapat)</span>
                                        @endif
                                    </div>
                                    <span class="text-xs font-mono font-bold text-gray-400">Urutan #{{ $komponen->urutan }}</span>
                                </div>

                                {{-- Pilihan Menu --}}
                                <div class="flex flex-wrap gap-2 pt-1">
                                    @if($komponen->tipe_komponen == 'tetap')
                                        @if($komponen->menu_terkait)
                                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border {{ $komponen->menu_terkait->resep_menu->count() > 0 ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-white' }} shadow-2xs">
                                                <span class="text-xs font-bold {{ $komponen->menu_terkait->resep_menu->count() > 0 ? 'text-emerald-900' : 'text-gray-800' }}">{{ $komponen->menu_terkait->nama_menu }}</span>
                                                <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider {{ $komponen->menu_terkait->resep_menu->count() > 0 ? 'bg-emerald-200 text-emerald-800' : 'bg-red-100 text-red-700' }}">
                                                    {{ $komponen->menu_terkait->resep_menu->count() > 0 ? 'Resep Lengkap' : 'Belum Lengkap' }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 italic">Menu tidak tertaut</span>
                                        @endif
                                    @else
                                        @forelse($komponen->opsi as $opsi)
                                            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border {{ $opsi->menu && $opsi->menu->resep_menu->count() > 0 ? 'border-emerald-200 bg-emerald-50' : 'border-gray-200 bg-white' }} shadow-2xs transition-all">
                                                <span class="text-xs font-bold text-gray-800">{{ $opsi->nama_pilihan }}</span>
                                                @if($opsi->menu)
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider {{ $opsi->menu->resep_menu->count() > 0 ? 'bg-emerald-200 text-emerald-800' : 'bg-red-100 text-red-700' }}">
                                                        {{ $opsi->menu->resep_menu->count() > 0 ? 'Resep Lengkap' : 'Belum Lengkap' }}
                                                    </span>
                                                @else
                                                    <span class="text-[10px] px-1.5 py-0.5 rounded-md font-bold uppercase tracking-wider bg-gray-200 text-gray-600">Tidak Tertaut</span>
                                                @endif
                                            </div>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Belum ada pilihan menu.</span>
                                        @endforelse
                                    @endif
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

            {{-- TAB 3: Kebutuhan Bahan Baku --}}
            <div x-show="tab === 'bahan'" x-cloak>
                <div class="bg-white rounded-xl border border-gray-200/80 shadow-2xs p-5 space-y-4">
                    <div class="border-b border-gray-100 pb-3 flex justify-between items-start">
                        <div>
                            <h3 class="text-sm font-extrabold text-gray-900">Kebutuhan Bahan Baku (Per 1 Porsi Paket)</h3>
                            <p class="text-xs text-gray-500 mt-1">Dihitung otomatis dari resep menu "Pasti Dapat" dan opsi pertama dari kelompok "Pilihan".</p>
                        </div>
                    </div>
                    
                    <div class="overflow-hidden rounded-xl border border-gray-200">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th scope="col" class="px-4 py-3 font-bold text-gray-600 w-12 text-center">No</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-gray-600">Bahan Baku</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-gray-600 text-right">Kebutuhan</th>
                                    <th scope="col" class="px-4 py-3 font-bold text-gray-600">Sumber Menu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                @forelse($kebutuhan as $idx => $bhn)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 text-center text-gray-500">{{ $idx + 1 }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-800">{{ $bhn['nama_bahan'] }}</td>
                                        <td class="px-4 py-3 text-right">
                                            <span class="font-black text-[#0D3024]">{{ $bhn['total_kebutuhan'] }}</span>
                                            <span class="text-xs font-bold text-gray-500 ml-1">{{ $bhn['satuan'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-xs text-gray-500">
                                            {{ $bhn['menu_nama'] }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <x-heroicon-o-beaker class="w-8 h-8 text-gray-300 mb-2" />
                                                <p class="text-sm font-bold">Belum ada resep bahan baku.</p>
                                                <p class="text-xs">Pastikan menu yang dipilih pada komponen memiliki data resep.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
