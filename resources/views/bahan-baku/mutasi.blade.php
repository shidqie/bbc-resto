{{-- 
    Halaman: Riwayat Mutasi Stok
    Deskripsi: Menampilkan pergerakan stok bahan baku (masuk, keluar, penyesuaian).
--}}
@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-[1200px] mx-auto space-y-6">
        
        {{-- Header --}}
        <x-ui.page-header 
            title="Riwayat Mutasi Stok" 
            subtitle="Pantau pergerakan stok bahan baku masuk, keluar, maupun penyesuaian."
            :breadcrumbs="['Bahan Baku', 'Stok Masuk / Keluar']">
            <x-slot:actions>
                <x-ui.button href="{{ route('bahan-baku.index') }}" variant="outline" icon="fa-boxes">Daftar Bahan Baku</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>
        
        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-ui.stat-card label="Total Transaksi" :value="$stats['total_transaksi']" icon="fa-exchange-alt" color="blue" />
            <x-ui.stat-card label="Stok Masuk (Hari Ini)" :value="$stats['masuk_hari_ini']" icon="fa-arrow-down" color="green" />
            <x-ui.stat-card label="Stok Keluar (Hari Ini)" :value="$stats['keluar_hari_ini']" icon="fa-arrow-up" color="red" />
        </div>
        
        {{-- Table --}}
        <x-ui.data-table :paginator="$mutasiStoks">
            {{-- Toolbar --}}
            <x-slot:toolbar>
                <form action="{{ route('mutasi-stok.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
                    <div class="relative w-full sm:w-56">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white transition-all">
                    </div>
                    
                    <select name="jenis_mutasi" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white min-w-[150px]">
                        <option value="">Semua Jenis</option>
                        <option value="masuk" {{ request('jenis_mutasi') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                        <option value="keluar" {{ request('jenis_mutasi') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                        <option value="penyesuaian" {{ request('jenis_mutasi') == 'penyesuaian' ? 'selected' : '' }}>Penyesuaian</option>
                    </select>

                    <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="px-4 py-2 border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none bg-white">
                    
                    <x-ui.button type="submit">Filter</x-ui.button>
                    @if(request()->hasAny(['search', 'jenis_mutasi', 'tanggal']))
                        <x-ui.button href="{{ route('mutasi-stok.index') }}" variant="outline">Reset</x-ui.button>
                    @endif
                </form>
            </x-slot:toolbar>

            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 text-gray-500 text-xs uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Waktu Transaksi</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Bahan Baku</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Jenis</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap text-right">Jumlah</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap text-right">Sisa Stok</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Keterangan</th>
                        <th class="px-6 py-4 font-semibold whitespace-nowrap">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($mutasiStoks as $mutasi)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                <div class="font-medium text-gray-900">{{ $mutasi->created_at->format('d M Y') }}</div>
                                <div class="text-xs">{{ $mutasi->created_at->format('H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $mutasi->bahanBaku->nama_bahan ?? '-' }}</div>
                                <div class="text-[11px] text-gray-500">{{ $mutasi->bahanBaku->kategoriBahan->nama_kategori ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($mutasi->jenis_mutasi == 'masuk')
                                    <x-ui.badge color="success" size="sm">Masuk</x-ui.badge>
                                @elseif($mutasi->jenis_mutasi == 'keluar')
                                    <x-ui.badge color="danger" size="sm">Keluar</x-ui.badge>
                                @else
                                    <x-ui.badge color="primary" size="sm">Penyesuaian</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @php
                                    $color = $mutasi->jenis_mutasi == 'masuk' ? 'text-emerald-600' : ($mutasi->jenis_mutasi == 'keluar' ? 'text-red-600' : 'text-blue-600');
                                    $sign = $mutasi->jenis_mutasi == 'masuk' ? '+' : ($mutasi->jenis_mutasi == 'keluar' ? '-' : '');
                                @endphp
                                <span class="font-bold {{ $color }}">{{ $sign }}{{ (float)$mutasi->jumlah }}</span>
                                <span class="text-xs text-gray-500">{{ $mutasi->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold text-gray-900">{{ (float)$mutasi->sisa_stok }}</span>
                                <span class="text-xs text-gray-500">{{ $mutasi->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $mutasi->keterangan ?? '-' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                        {{ substr($mutasi->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-medium text-gray-700">{{ $mutasi->user->name ?? 'Sistem' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-ui.empty-state icon="fa-history" title="Belum ada riwayat mutasi stok." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </x-ui.data-table>
    </div>
</div>
@endsection
