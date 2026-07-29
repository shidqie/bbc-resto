{{-- 
    Halaman: Riwayat Pengadaan Bahan Baku
    UI: disamakan dengan Kelola Menu
--}}
@extends('layouts.pos')

@section('title', 'Riwayat Pengadaan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3 mb-2">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Pengadaan Bahan Baku</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola permintaan pembelian dan penerimaan stok gudang</p>
            </div>
        </div>

        {{-- TAB NAVIGASI / SUB MENU --}}
        <div class="flex items-center gap-1 bg-white border border-gray-200 p-1.5 rounded-xl mb-4 w-max shadow-sm">
            <a href="{{ route('pengadaan.index') }}" class="px-4 py-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('pengadaan.index') ? 'bg-[#0F2E23] text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                Riwayat Pengadaan
            </a>
            <a href="{{ route('pengadaan.create') }}" class="px-4 py-2 text-sm font-bold rounded-lg transition-colors {{ request()->routeIs('pengadaan.create') ? 'bg-[#0F2E23] text-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100' }}">
                Buat Pembelian Bahan Baku
            </a>
        </div>

        <x-ui.alert />

        {{-- Terima Barang dari Pembelian --}}
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4">
            <h3 class="text-sm font-bold text-gray-900 mb-2">Terima Bahan Baku (Input ID Pembelian)</h3>
            <form action="{{ route('pengadaan.terima-barang') }}" method="GET" class="flex gap-2 w-full md:w-1/2">
                <input type="text" name="nomor_pengadaan" required placeholder="Contoh: PO-2026..." class="flex-1 px-3 py-2 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 transition-all bg-gray-50">
                <button type="submit" class="px-4 py-2 bg-[#0F2E23] hover:bg-[#0a1f17] text-white rounded-lg text-xs font-bold transition-colors whitespace-nowrap">
                    Cari & Terima
                </button>
            </form>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-2 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Total Transaksi Pengadaan</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-4 py-3">
                <p class="text-xs font-medium text-gray-500">Total Biaya Pengadaan</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($stats['total_biaya'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="flex flex-col sm:flex-row gap-2 items-start sm:items-center justify-between mb-3 shrink-0 mt-3">
            <form action="{{ route('pengadaan.index') }}" method="GET" class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <div class="relative flex-1 sm:flex-none sm:w-56">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nomor PO / Asal…" class="w-full pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all bg-white">
                </div>
                <button type="submit" class="text-xs font-medium bg-white border border-gray-200 text-gray-600 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shrink-0">Cari</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">No. Pengadaan &amp; Tanggal</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3 text-left">Pencatat</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengadaans as $i => $po)
                    <tr class="hover:bg-gray-50/60 transition-colors group">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium align-middle">{{ $pengadaans->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 font-mono text-xs">{{ $po->nomor_pengadaan }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $po->tanggal_pengadaan->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 leading-tight">{{ $po->asal_pembelian ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($po->status === 'diterima')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-700">Diterima</span>
                            @elseif($po->status === 'dibatalkan')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700">Dibatalkan</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-700">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-600 text-xs">{{ $po->user->name ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-600">Rp {{ number_format($po->total_biaya, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1 opacity-70 group-hover:opacity-100 transition-opacity">
                                @if($po->status === 'pending')
                                <a href="{{ route('pengadaan.form-terima', $po->id) }}" class="p-1.5 rounded-md text-emerald-600 hover:bg-emerald-50 transition-colors" title="Terima Barang">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </a>
                                @endif
                                <a href="{{ route('pengadaan.show', $po->id) }}" class="p-1.5 rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-900 transition-colors" title="Detail &amp; Realisasi">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Tidak ada data pengadaan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4 shrink-0">{{ $pengadaans->links() }}</div>

    </div>
</div>
@endsection
