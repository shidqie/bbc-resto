{{-- 
    Halaman: Riwayat Mutasi Stok
    UI: disamakan dengan Kelola Menu
--}}
@extends('layouts.pos')

@section('title', 'Stok Masuk / Keluar')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Stok Masuk / Keluar</h1>
                <p class="text-xs text-gray-500 mt-0.5">Pantau pergerakan stok bahan baku masuk, keluar, maupun penyesuaian.</p>
            </div>
            <a href="{{ route('bahan-baku.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Daftar Bahan Baku
            </a>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Total Transaksi</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['total_transaksi'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Masuk (Hari Ini)</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">{{ $stats['masuk_hari_ini'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Stok Keluar (Hari Ini)</p>
                <p class="text-xl font-bold text-red-500 mt-1">{{ $stats['keluar_hari_ini'] }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0">
            <form action="{{ route('mutasi-stok.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari bahan baku..." class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all bg-white">
                </div>
                <select name="jenis_mutasi" class="text-xs border border-gray-200 rounded-lg bg-white px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="masuk" {{ request('jenis_mutasi') == 'masuk' ? 'selected' : '' }}>Masuk</option>
                    <option value="keluar" {{ request('jenis_mutasi') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                    <option value="penyesuaian" {{ request('jenis_mutasi') == 'penyesuaian' ? 'selected' : '' }}>Penyesuaian</option>
                </select>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="text-xs border border-gray-200 rounded-lg px-3 py-2 outline-none focus:ring-1 focus:ring-gray-400 transition-all bg-white" onchange="this.form.submit()">
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
                @if(request()->hasAny(['search', 'jenis_mutasi', 'tanggal']))
                    <a href="{{ route('mutasi-stok.index') }}" class="text-xs font-medium text-red-500 hover:text-red-700 px-2 py-2 rounded-lg hover:bg-red-50 transition-colors shrink-0">Reset</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Waktu Transaksi</th>
                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-right">Sisa Stok</th>
                        <th class="px-4 py-3 text-left">Referensi</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                        <th class="px-4 py-3 text-left">Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($mutasiStoks as $i => $mutasi)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">{{ $mutasiStoks->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 text-xs">{{ $mutasi->created_at->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400">{{ $mutasi->created_at->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $mutasi->bahanBaku->nama_bahan ?? '-' }}</p>
                            <p class="text-[10px] text-gray-400">{{ $mutasi->bahanBaku->kategoriBahan->nama_kategori ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($mutasi->jenis_mutasi == 'masuk')
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700">Masuk</span>
                            @elseif($mutasi->jenis_mutasi == 'keluar')
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-red-50 text-red-700">Keluar</span>
                            @else
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700">Penyesuaian</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @php
                                $color = $mutasi->jenis_mutasi == 'masuk' ? 'text-emerald-600' : ($mutasi->jenis_mutasi == 'keluar' ? 'text-red-600' : 'text-blue-600');
                                $sign = $mutasi->jenis_mutasi == 'masuk' ? '+' : ($mutasi->jenis_mutasi == 'keluar' ? '-' : '');
                            @endphp
                            <span class="font-bold {{ $color }}">{{ $sign }}{{ (float)$mutasi->jumlah }}</span>
                            <span class="text-xs text-gray-400"> {{ $mutasi->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold text-gray-900">{{ (float)$mutasi->sisa_stok }}</span>
                            <span class="text-xs text-gray-400"> {{ $mutasi->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($mutasi->referensi)
                                <span class="inline-block text-xs font-mono font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-700">{{ $mutasi->referensi }}</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">{{ $mutasi->keterangan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-[10px] font-bold text-gray-600">
                                    {{ substr($mutasi->user->name ?? 'S', 0, 1) }}
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $mutasi->user->name ?? 'Sistem' }}</span>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Belum ada riwayat mutasi stok.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 shrink-0">{{ $mutasiStoks->links() }}</div>

    </div>
</div>
@endsection
