{{-- 
    Halaman: Detail Menu
    Deskripsi: Menampilkan informasi lengkap menu beserta resep/komposisinya.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Detail Menu" 
            :breadcrumbs="['Menu', 'Daftar Menu', 'Detail']">
            <x-slot:actions>
                <x-ui.button href="{{ route('menu.index') }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
                <x-ui.button href="{{ route('menu.edit', $menu->id) }}" icon="pencil-square">Edit Menu</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Informasi Menu --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    @if($menu->foto)
                        <img src="{{ Storage::url($menu->foto) }}" class="w-full h-64 object-cover">
                    @else
                        <div class="w-full h-64 bg-gray-100 flex flex-col items-center justify-center text-gray-400">
                            <x-heroicon-o-photo class="text-4xl mb-2 w-[1em] h-[1em] inline-block shrink-0" />
                            <span class="text-sm font-medium">Tidak ada foto</span>
                        </div>
                    @endif
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">{{ $menu->nama }}</h2>
                                <p class="text-sm text-gray-500">{{ $menu->kategori->nama_kategori ?? 'Tanpa Kategori' }}</p>
                            </div>
                            @if($menu->status == 'tersedia')
                                <x-ui.badge color="success" dot>Tersedia</x-ui.badge>
                            @else
                                <x-ui.badge color="danger" dot>Habis</x-ui.badge>
                            @endif
                        </div>
                        
                        <div class="text-2xl font-black text-[#3B82F6] mb-4">
                            Rp {{ number_format($menu->harga, 0, ',', '.') }}
                        </div>

                        <div class="space-y-3 pt-4 border-t border-gray-100">
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-1">Jenis Menu</div>
                                <div class="text-sm font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $menu->jenis_menu) }}</div>
                            </div>
                            @if($menu->deskripsi)
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-1">Deskripsi</div>
                                <div class="text-sm text-gray-700">{{ $menu->deskripsi }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Komposisi Resep --}}
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden h-full flex flex-col">
                    <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                        <h2 class="text-lg font-bold text-gray-900">Komposisi Resep</h2>
                        <a href="{{ route('resep.create', $menu->id) }}" class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-xl font-medium text-sm transition-colors">
                            <x-heroicon-o-list-bullet class="w-5 h-5 inline-block shrink-0" /> Atur Resep
                        </a>
                    </div>
                    
                    <div class="p-0 flex-1 overflow-x-auto">
                        @if($menu->resep->count() > 0)
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                                        <th class="px-6 py-4 font-semibold">Nama Bahan Baku</th>
                                        <th class="px-6 py-4 font-semibold text-right">Kebutuhan per Porsi</th>
                                        <th class="px-6 py-4 font-semibold">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-sm">
                                    @foreach($menu->resep as $resep)
                                        <tr class="hover:bg-gray-50/50 transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-medium text-gray-900">{{ $resep->bahanBaku->nama_bahan }}</div>
                                                <div class="text-xs text-gray-500">{{ $resep->bahanBaku->kategoriBahan->nama_kategori ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 font-semibold text-gray-900 text-right">
                                                {{ (float)$resep->jumlah_kebutuhan }} {{ $resep->satuan }}
                                            </td>
                                            <td class="px-6 py-4 text-gray-500">
                                                {{ $resep->keterangan ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="py-12">
                                <x-ui.empty-state 
                                    icon="document-text" 
                                    title="Belum Ada Resep" 
                                    description="Anda belum mengatur komposisi bahan baku untuk menu ini. Resep diperlukan agar stok bahan otomatis berkurang saat menu dipesan.">
                                    <x-slot:action>
                                        <x-ui.button href="{{ route('resep.create', $menu->id) }}" variant="primary">Buat Resep Sekarang</x-ui.button>
                                    </x-slot:action>
                                </x-ui.empty-state>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
