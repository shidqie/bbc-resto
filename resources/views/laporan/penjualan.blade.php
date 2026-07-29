@extends('layouts.pos')

@section('title', 'Laporan Penjualan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Laporan Penjualan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Rekapitulasi penjualan terintegrasi (Reguler, Catering, Nasi Box)</p>
            </div>
            <a href="{{ route('laporan.penjualan.cetak', ['start_date' => $startDate, 'end_date' => $endDate, 'jenis' => $jenisFilter]) }}" target="_blank"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-red-600 rounded-lg px-3 py-2 hover:bg-red-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Cetak PDF
            </a>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-xs font-medium text-gray-500">Total Pendapatan</p>
                <p class="text-xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-xs font-medium text-gray-500">Total Transaksi</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $totalTransaksi }}</p>
            </div>
        </div>

        {{-- Filter Bar --}}
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <form action="{{ route('laporan.penjualan') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Pesanan</label>
                    <select name="jenis" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                        <option value="semua" {{ $jenisFilter === 'semua' ? 'selected' : '' }}>Semua Jenis</option>
                        <option value="reguler" {{ $jenisFilter === 'reguler' ? 'selected' : '' }}>Reguler (Dine-In)</option>
                        <option value="catering" {{ $jenisFilter === 'catering' ? 'selected' : '' }}>Catering</option>
                        <option value="nasibox" {{ $jenisFilter === 'nasibox' ? 'selected' : '' }}>Nasi Box</option>
                    </select>
                </div>
                <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-4 py-2 hover:bg-gray-800 transition-colors shrink-0">Filter</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">No. Pesanan</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-right">Total Harga</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pesanans as $i => $pesanan)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ \Carbon\Carbon::parse($pesanan->tanggal)->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900 font-mono text-xs">{{ $pesanan->kode }}</td>
                        <td class="px-4 py-3 text-gray-700 font-medium">{{ $pesanan->pelanggan }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700">{{ $pesanan->jenis }}</span>
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp {{ number_format($pesanan->total, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Tidak ada data penjualan pada periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pesanans->hasPages())
        <div class="mt-4 shrink-0">{{ $pesanans->links() }}</div>
        @endif

    </div>
</div>
@endsection
