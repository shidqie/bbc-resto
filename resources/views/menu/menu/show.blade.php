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
            :breadcrumbs="['Manajemen Menu', 'Data Menu', 'Detail']">
            <x-slot:actions>
                <x-ui.button href="{{ route('menu.index') }}" variant="outline" icon="arrow-left">Kembali</x-ui.button>
                <x-ui.button href="{{ route('menu.edit', $menu->id) }}" icon="pencil-square">Edit Menu</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <x-ui.alert />

        <div x-data="{ activeTab: 'informasi' }" class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
            {{-- Tab Headers --}}
            <div class="flex border-b border-gray-100">
                <button @click="activeTab = 'informasi'" 
                        :class="activeTab === 'informasi' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-4 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none">
                    Informasi Menu
                </button>
                <button @click="activeTab = 'resep'" 
                        :class="activeTab === 'resep' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="px-6 py-4 border-b-2 font-semibold text-sm transition-colors outline-none focus:outline-none">
                    Resep Bahan Baku
                </button>
            </div>

            {{-- Tab Contents --}}
            <div class="p-6">
                {{-- Informasi Menu Tab --}}
                <div x-show="activeTab === 'informasi'" class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        @if($menu->foto)
                            <img src="{{ Storage::url($menu->foto) }}" class="w-full h-auto rounded-xl object-cover border border-gray-100">
                        @else
                            <div class="w-full h-64 bg-gray-50 rounded-xl flex flex-col items-center justify-center text-gray-400 border border-gray-100">
                                <x-heroicon-o-photo class="text-4xl mb-2 w-[1em] h-[1em] inline-block shrink-0" />
                                <span class="text-sm font-medium">Tidak ada foto</span>
                            </div>
                        @endif
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">{{ $menu->nama_menu }}</h2>
                                <p class="text-sm text-gray-500 mt-1">{{ $menu->kategori_menu->nama_kategori ?? 'Tanpa Kategori' }}</p>
                            </div>
                            @if($menu->status_aktif)
                                <x-ui.badge color="success" dot>Tersedia</x-ui.badge>
                            @else
                                <x-ui.badge color="danger" dot>Habis</x-ui.badge>
                            @endif
                        </div>
                        
                        <div class="text-3xl font-black text-[#3B82F6]">
                            Rp {{ number_format($menu->harga_jual, 0, ',', '.') }}
                        </div>

                        <div class="space-y-4 pt-4 border-t border-gray-100">
                            <div>
                                <div class="text-sm font-semibold text-gray-500 mb-1">Layanan</div>
                                <div class="text-base font-medium text-gray-900 capitalize">
                                    @php $jk = strtolower($menu->jenis_menu->kode_jenis ?? ''); @endphp
                                    @if($jk == 'dine_in' || $jk == 'reguler') Dine In
                                    @elseif($jk == 'catering') Katering
                                    @elseif($jk == 'nasi_box') Nasi Box
                                    @else {{ $menu->jenis_menu->nama_jenis ?? '-' }} @endif
                                </div>
                            </div>
                            @if($menu->deskripsi)
                            <div>
                                <div class="text-sm font-semibold text-gray-500 mb-1">Deskripsi</div>
                                <div class="text-base text-gray-700 leading-relaxed">{{ $menu->deskripsi }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Resep Bahan Baku Tab --}}
                <div x-show="activeTab === 'resep'" style="display: none;">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Komposisi Resep</h3>
                        <a href="{{ route('menu.edit', $menu->id) }}" class="inline-flex items-center gap-2 bg-blue-50 text-blue-600 hover:bg-blue-100 px-4 py-2 rounded-xl font-medium text-sm transition-colors">
                            <x-heroicon-o-pencil-square class="w-5 h-5 inline-block shrink-0" /> Edit Resep
                        </a>
                    </div>
                    
                    <div class="bg-gray-50/50 rounded-xl border border-gray-100 overflow-hidden">
                        @if($menu->resep_menu && $menu->resep_menu->count() > 0)
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-gray-100 text-gray-600 text-sm tracking-wider border-b border-gray-200">
                                        <th class="px-6 py-4 font-bold">Nama Bahan Baku</th>
                                        <th class="px-6 py-4 font-bold text-right">Takaran per Porsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 text-sm">
                                    @foreach($menu->resep_menu as $resep)
                                        <tr class="hover:bg-white transition-colors">
                                            <td class="px-6 py-4">
                                                <div class="font-bold text-gray-900">{{ $resep->bahan_baku->nama_bahan ?? '-' }}</div>
                                                <div class="text-xs text-gray-500 font-medium">{{ $resep->bahan_baku->kategoriBahan->nama_kategori ?? '-' }}</div>
                                            </td>
                                            <td class="px-6 py-4 font-black text-gray-900 text-right text-lg">
                                                {{ (float)($resep->jumlah_kebutuhan ?? $resep->jumlah) }} <span class="text-sm font-semibold text-gray-500">{{ $resep->bahan_baku->satuan->nama_satuan ?? '-' }}</span>
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
                                        <x-ui.button href="{{ route('menu.edit', $menu->id) }}" variant="primary">Atur Resep Sekarang</x-ui.button>
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
