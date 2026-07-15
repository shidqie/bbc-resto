{{-- 
    Halaman: Stok Menipis
    Deskripsi: Daftar bahan baku yang perlu segera di-restock karena 
               sudah mencapai atau melewati batas minimum.
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Bahan Baku Stok Menipis" 
            subtitle="Daftar bahan baku yang perlu segera di-restock karena sudah mencapai atau melewati batas minimum."
            :breadcrumbs="['Bahan Baku', 'Stok Menipis']">
            <x-slot:actions>
                <x-ui.button href="{{ route('bahan-baku.index') }}" variant="outline" icon="fa-boxes">Semua Bahan Baku</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>
        
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.stat-card label="Stok Menipis (Warning)" :value="$stats['total_menipis']" icon="fa-exclamation-triangle" color="orange" />
            <x-ui.stat-card label="Stok Habis (Kritis)" :value="$stats['total_habis']" icon="fa-times-circle" color="red" />
        </div>
        
        {{-- Table --}}
        <x-ui.data-table :paginator="$bahanBakus">
            {{-- Toolbar --}}
            <x-slot:toolbar>
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <form action="{{ route('stok-menipis.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                        <div class="relative w-full sm:w-64">
                            <x-heroicon-o-magnifying-glass class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 w-5 h-5 inline-block shrink-0" />
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                        </div>
                        
                        <select name="kategori" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white min-w-[150px]">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" {{ request('kategori') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        
                        <x-ui.button type="submit">Filter</x-ui.button>
                        @if(request()->hasAny(['search', 'kategori']))
                            <x-ui.button href="{{ route('stok-menipis.index') }}" variant="outline">Reset</x-ui.button>
                        @endif
                    </form>
                    
                    <x-ui.button href="{{ route('pengadaan.create') }}" icon="fa-truck" variant="primary">Buat Pengadaan</x-ui.button>
                </div>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Bahan Baku</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Kategori</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap text-right">Sisa Stok</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap text-right">Batas Min.</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($bahanBakus as $bahan)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $bahan->nama_bahan }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $bahan->kategoriBahan->nama_kategori ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php
                                    $isHabis = $bahan->stok <= 0;
                                    $textColor = $isHabis ? 'text-red-600' : 'text-orange-600';
                                @endphp
                                <span class="font-bold text-lg {{ $textColor }}">{{ (float)$bahan->stok }}</span>
                                <span class="text-xs text-gray-500">{{ $bahan->satuan->nama_satuan ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-500 font-medium">
                                {{ (float)$bahan->stok_minimum }} <span class="text-xs">{{ $bahan->satuan->nama_satuan ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($isHabis)
                                    <x-ui.badge color="danger" dot>Stok Habis</x-ui.badge>
                                @else
                                    <x-ui.badge color="warning" dot>Menipis</x-ui.badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state icon="fa-check-circle" title="Semua stok bahan baku terpantau aman." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>
    </div>
</div>
@endsection
