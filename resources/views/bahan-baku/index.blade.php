{{-- 
    Halaman: Daftar Bahan Baku
    Deskripsi: Menampilkan semua data bahan baku dengan filter, statistik, dan aksi CRUD.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1600px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Daftar Bahan Baku" 
            subtitle="Kelola data dan pantau persediaan bahan baku rumah makan."
            :breadcrumbs="['Bahan Baku', 'Daftar Bahan Baku']">
            <x-slot:actions>
                <x-ui.button href="{{ route('bahan-baku.create') }}" icon="fa-plus">Tambah Bahan Baku</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- Statistik --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <x-ui.stat-card label="Total Bahan Baku" :value="$totalBahan" icon="fa-boxes" color="blue" />
            <x-ui.stat-card label="Stok Aman" :value="$stokAman" icon="fa-check-circle" color="green" />
            <x-ui.stat-card label="Stok Menipis" :value="$stokMenipis" icon="fa-exclamation-triangle" color="orange" />
            <x-ui.stat-card label="Stok Habis" :value="$stokHabis" icon="fa-times-circle" color="red" />
        </div>

        {{-- Alert --}}
        <x-ui.alert />

        {{-- Tabel Data --}}
        <x-ui.data-table :paginator="$bahanBakus">
            {{-- Toolbar: Search & Filter --}}
            <x-slot:toolbar>
                <form method="GET" action="{{ route('bahan-baku.index') }}" class="flex flex-col lg:flex-row justify-between gap-4">
                    {{-- Search --}}
                    <div class="relative w-full lg:w-96">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="bg-white border border-gray-200 text-gray-900 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] block w-full pl-10 p-2.5 transition-colors outline-none" 
                               placeholder="Cari kode atau nama bahan...">
                    </div>
                    
                    {{-- Filter --}}
                    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                        <select name="kategori" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] p-2.5 flex-1 sm:flex-none outline-none" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        <select name="status_stok" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] p-2.5 flex-1 sm:flex-none outline-none" onchange="this.form.submit()">
                            <option value="">Semua Status Stok</option>
                            <option value="aman" {{ request('status_stok') == 'aman' ? 'selected' : '' }}>Aman</option>
                            <option value="menipis" {{ request('status_stok') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                            <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>Habis</option>
                        </select>
                        <select name="supplier" class="bg-white border border-gray-200 text-gray-700 text-sm rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] p-2.5 flex-1 sm:flex-none outline-none" onchange="this.form.submit()">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>{{ $sup->nama_supplier }}</option>
                            @endforeach
                        </select>
                        @if(request()->hasAny(['search', 'kategori', 'status_stok', 'supplier']))
                            <a href="{{ route('bahan-baku.index') }}" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors" title="Reset Filter">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="hidden"></button>
                    </div>
                </form>
            </x-slot:toolbar>

            {{-- Tabel --}}
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-4 font-semibold w-12 text-center">No</th>
                        <th class="px-6 py-4 font-semibold">Bahan Baku</th>
                        <th class="px-6 py-4 font-semibold">Kategori</th>
                        <th class="px-6 py-4 font-semibold text-right">Stok</th>
                        <th class="px-6 py-4 font-semibold text-right">Batas Min.</th>
                        <th class="px-6 py-4 font-semibold text-right">Harga Terakhir</th>
                        <th class="px-6 py-4 font-semibold">Supplier</th>
                        <th class="px-6 py-4 font-semibold text-center">Status</th>
                        <th class="px-6 py-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($bahanBakus as $i => $item)
                    <tr class="hover:bg-gray-50 transition-colors group {{ !$item->status ? 'opacity-50' : '' }}">
                        <td class="px-6 py-3 text-center text-gray-500">{{ $bahanBakus->firstItem() + $i }}</td>
                        <td class="px-6 py-3">
                            <div class="font-bold text-gray-900">{{ $item->nama_bahan }}</div>
                            <div class="text-xs text-gray-400">{{ $item->kode_bahan }}</div>
                        </td>
                        <td class="px-6 py-3 text-gray-600">{{ $item->kategoriBahan->nama_kategori }}</td>
                        <td class="px-6 py-3 text-right">
                            <div class="font-bold {{ $item->stok <= 0 ? 'text-red-600' : ($item->stok <= $item->stok_minimum ? 'text-yellow-600' : 'text-gray-900') }}">
                                {{ rtrim(rtrim(number_format($item->stok, 2, ',', '.'), '0'), ',') }} 
                                <span class="text-xs font-normal text-gray-500">{{ $item->satuan->singkatan }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-right text-gray-500">
                            {{ rtrim(rtrim(number_format($item->stok_minimum, 2, ',', '.'), '0'), ',') }} {{ $item->satuan->singkatan }}
                        </td>
                        <td class="px-6 py-3 text-right text-gray-700">
                            Rp{{ number_format($item->harga_terakhir, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $item->supplier->nama_supplier ?? '-' }}</td>
                        <td class="px-6 py-3 text-center">
                            @if(!$item->status)
                                <x-ui.badge color="gray" size="sm">Nonaktif</x-ui.badge>
                            @elseif($item->stok <= 0)
                                <x-ui.badge color="danger" size="sm">Habis</x-ui.badge>
                            @elseif($item->stok <= $item->stok_minimum)
                                <x-ui.badge color="warning" size="sm">Menipis</x-ui.badge>
                            @else
                                <x-ui.badge color="success" size="sm">Aman</x-ui.badge>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                <button @click="open = !open" @click.away="open = false" class="p-2 text-gray-400 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                
                                <div x-show="open" style="display: none;" class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 z-50 py-1.5 overflow-hidden">
                                    <a href="{{ route('bahan-baku.show', $item->id) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i class="fas fa-eye w-4 text-gray-400 mr-2"></i> Detail
                                    </a>
                                    <a href="{{ route('bahan-baku.edit', $item->id) }}" class="block px-4 py-2 text-sm text-blue-600 hover:bg-blue-50">
                                        <i class="fas fa-edit w-4 text-blue-400 mr-2"></i> Edit Data
                                    </a>
                                    <div class="h-px bg-gray-100 my-1"></div>
                                    @if($item->status)
                                    <form action="{{ route('bahan-baku.update', $item->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="nama_bahan" value="{{ $item->nama_bahan }}">
                                        <input type="hidden" name="kategori_bahan_id" value="{{ $item->kategori_bahan_id }}">
                                        <input type="hidden" name="satuan_id" value="{{ $item->satuan_id }}">
                                        <input type="hidden" name="stok_minimum" value="{{ $item->stok_minimum }}">
                                        <input type="hidden" name="harga_terakhir" value="{{ $item->harga_terakhir }}">
                                        <input type="hidden" name="status" value="0">
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-orange-600 hover:bg-orange-50" onclick="return confirm('Nonaktifkan bahan baku ini?')">
                                            <i class="fas fa-ban w-4 mr-2"></i> Nonaktifkan
                                        </button>
                                    </form>
                                    @else
                                    <form action="{{ route('bahan-baku.update', $item->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="nama_bahan" value="{{ $item->nama_bahan }}">
                                        <input type="hidden" name="kategori_bahan_id" value="{{ $item->kategori_bahan_id }}">
                                        <input type="hidden" name="satuan_id" value="{{ $item->satuan_id }}">
                                        <input type="hidden" name="stok_minimum" value="{{ $item->stok_minimum }}">
                                        <input type="hidden" name="harga_terakhir" value="{{ $item->harga_terakhir }}">
                                        <input type="hidden" name="status" value="1">
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-emerald-600 hover:bg-emerald-50">
                                            <i class="fas fa-check w-4 mr-2"></i> Aktifkan
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('bahan-baku.destroy', $item->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50" onclick="return confirm('Hapus permanen bahan baku ini?')">
                                            <i class="fas fa-trash w-4 mr-2"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <x-ui.empty-state 
                                icon="fa-box-open" 
                                :title="request()->hasAny(['search', 'kategori', 'status_stok', 'supplier']) ? 'Data tidak ditemukan' : 'Belum ada bahan baku'"
                                message="Tambahkan data bahan baku untuk mengelola stok.">
                                @if(request()->hasAny(['search', 'kategori', 'status_stok', 'supplier']))
                                    <x-ui.button href="{{ route('bahan-baku.index') }}" variant="outline" icon="fa-times" size="sm">Reset Filter</x-ui.button>
                                @else
                                    <x-ui.button href="{{ route('bahan-baku.create') }}" icon="fa-plus" size="sm">Tambah Bahan Baku</x-ui.button>
                                @endif
                            </x-ui.empty-state>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>
    </div>
</div>
@endsection
