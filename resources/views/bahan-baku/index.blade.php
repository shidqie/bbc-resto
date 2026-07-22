{{-- 
    Halaman: Daftar Bahan Baku (Terpisah Resto & Nasi Box vs Catering)
    Deskripsi: Mengelola persediaan bahan baku dengan pengelompokan Resto & Nasi Box (Harian) dan Catering.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1600px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Daftar Bahan Baku" 
            subtitle="Kelola persediaan bahan baku (Stok Harian Resto & Nasi Box terpisah dengan Stok Catering)"
            :breadcrumbs="['Bahan Baku', 'Daftar Bahan Baku']">
            <x-slot:actions>
                <x-ui.button href="{{ route('bahan-baku.create') }}" icon="fa-plus">Tambah Bahan Baku Baru</x-ui.button>
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

        {{-- Filter Peruntukan Tabs --}}
        <div class="flex items-center gap-2 bg-white p-2 rounded-2xl border border-gray-200/80 shadow-2xs overflow-x-auto">
            <span class="text-xs font-extrabold text-gray-500 uppercase tracking-wider px-3 shrink-0">Peruntukan Stock:</span>
            
            <a href="{{ route('bahan-baku.index', request()->except('jenis_penggunaan')) }}" 
               class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap {{ !request('jenis_penggunaan') ? 'bg-[#0F2E23] text-white shadow-xs' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
               Semua Bahan Baku ({{ $statsPenggunaan['total'] }})
            </a>
            
            <a href="{{ route('bahan-baku.index', array_merge(request()->all(), ['jenis_penggunaan' => 'resto_nasibox'])) }}" 
               class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap {{ request('jenis_penggunaan') === 'resto_nasibox' ? 'bg-emerald-800 text-white shadow-xs' : 'bg-emerald-50 text-emerald-900 border border-emerald-200' }}">
               Resto & Nasi Box ({{ $statsPenggunaan['resto_nasibox'] }})
            </a>
            
            <a href="{{ route('bahan-baku.index', array_merge(request()->all(), ['jenis_penggunaan' => 'catering'])) }}" 
               class="px-4 py-2 rounded-xl text-xs font-extrabold transition-all whitespace-nowrap {{ request('jenis_penggunaan') === 'catering' ? 'bg-blue-800 text-white shadow-xs' : 'bg-blue-50 text-blue-900 border border-blue-200' }}">
               Catering ({{ $statsPenggunaan['catering'] }})
            </a>
        </div>

        {{-- Tabel Data --}}
        <x-ui.data-table :paginator="$bahanBakus">
            {{-- Toolbar: Search & Filter --}}
            <x-slot:toolbar>
                <form method="GET" action="{{ route('bahan-baku.index') }}" class="flex flex-col lg:flex-row justify-between gap-4 w-full">
                    @if(request('jenis_penggunaan'))
                        <input type="hidden" name="jenis_penggunaan" value="{{ request('jenis_penggunaan') }}">
                    @endif
                    
                    {{-- Search --}}
                    <div class="relative w-full lg:w-96">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="text-gray-400 w-4 h-4 inline-block shrink-0" />
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="bg-white border border-gray-200 text-gray-900 text-xs rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] block w-full pl-10 p-2.5 transition-colors outline-none font-medium" 
                               placeholder="Cari kode atau nama bahan…">
                    </div>
                    
                    {{-- Filter --}}
                    <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
                        <select name="kategori" class="bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] p-2.5 outline-none" onchange="this.form.submit()">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        
                        <select name="status_stok" class="bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] p-2.5 outline-none" onchange="this.form.submit()">
                            <option value="">Semua Status Stok</option>
                            <option value="aman" {{ request('status_stok') == 'aman' ? 'selected' : '' }}>Aman</option>
                            <option value="menipis" {{ request('status_stok') == 'menipis' ? 'selected' : '' }}>Menipis</option>
                            <option value="habis" {{ request('status_stok') == 'habis' ? 'selected' : '' }}>Habis</option>
                        </select>
                        
                        <select name="supplier" class="bg-white border border-gray-200 text-gray-700 text-xs font-bold rounded-xl focus:ring-2 focus:ring-[#0F2E23]/10 focus:border-[#0F2E23] p-2.5 outline-none" onchange="this.form.submit()">
                            <option value="">Semua Supplier</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ request('supplier') == $sup->id ? 'selected' : '' }}>{{ $sup->nama_supplier }}</option>
                            @endforeach
                        </select>

                        @if(request()->hasAny(['search', 'kategori', 'status_stok', 'supplier', 'jenis_penggunaan']))
                            <a href="{{ route('bahan-baku.index') }}" class="p-2.5 bg-red-50 text-red-600 rounded-xl hover:bg-red-100 transition-colors" title="Reset Filter">
                                <x-heroicon-o-x-mark class="w-4 h-4 inline-block shrink-0" />
                            </a>
                        @endif
                    </div>
                </form>
            </x-slot:toolbar>

            {{-- Tabel --}}
            <table class="w-full text-sm text-left whitespace-nowrap">
                <thead class="text-xs text-gray-500 uppercase bg-gray-50/50 border-b border-gray-100 font-bold">
                    <tr>
                        <th class="px-5 py-4 w-12 text-center">No</th>
                        <th class="px-5 py-4">Kode & Nama Bahan</th>
                        <th class="px-5 py-4">Peruntukan Stok</th>
                        <th class="px-5 py-4">Kategori</th>
                        <th class="px-5 py-4 text-right">Stok Saat Ini</th>
                        <th class="px-5 py-4 text-right">Stok Min.</th>
                        <th class="px-5 py-4 text-right">Harga Terakhir</th>
                        <th class="px-5 py-4 text-center">Status</th>
                        <th class="px-5 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($bahanBakus as $i => $item)
                    <tr class="hover:bg-gray-50/50 transition-colors {{ !$item->status ? 'opacity-50' : '' }}">
                        <td class="px-5 py-4 text-center text-gray-500 font-bold">{{ $bahanBakus->firstItem() + $i }}</td>
                        <td class="px-5 py-4">
                            <div class="font-bold text-gray-900">{{ $item->nama_bahan }}</div>
                            <div class="text-xs font-mono font-bold text-[#0F2E23]">{{ $item->kode_bahan }}</div>
                        </td>
                        <td class="px-5 py-4">
                            @if($item->jenis_penggunaan === 'catering')
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">Catering</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">Resto & Nasi Box</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-gray-600 font-medium">{{ $item->kategoriBahan->nama_kategori ?? '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <span class="font-extrabold text-[#0F2E23]">{{ number_format($item->stok, 2, ',', '.') }}</span>
                            <span class="text-xs text-gray-500 font-medium">{{ $item->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-5 py-4 text-right text-gray-500 font-semibold">
                            {{ number_format($item->stok_minimum, 2, ',', '.') }} {{ $item->satuan->nama_satuan ?? '' }}
                        </td>
                        <td class="px-5 py-4 text-right font-extrabold text-gray-900">
                            Rp {{ number_format($item->harga_terakhir, 0, ',', '.') }}
                        </td>
                        <td class="px-5 py-4 text-center">
                            @if ($item->stok <= 0)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">Habis</span>
                            @elseif ($item->stok <= $item->stok_minimum)
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300">Menipis</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 border border-emerald-200">Aman</span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-center">
                            <div class="flex justify-center items-center gap-1.5">
                                <a href="{{ route('bahan-baku.show', $item->id) }}" class="p-1.5 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors" title="Detail">
                                    <x-heroicon-o-eye class="w-4 h-4" />
                                </a>
                                <a href="{{ route('bahan-baku.edit', $item->id) }}" class="p-1.5 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                    <x-heroicon-o-pencil-square class="w-4 h-4" />
                                </a>
                                <form action="{{ route('bahan-baku.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus bahan baku ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                        <x-heroicon-o-trash class="w-4 h-4" />
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <x-ui.empty-state icon="fa-boxes" title="Belum ada data bahan baku" />
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>

    </div>
</div>
@endsection
