@extends('layouts.pos')

@section('title', 'Laporan Pengadaan Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Laporan Pengadaan Bahan Baku</h1>
                <p class="text-xs text-gray-500 mt-0.5">Rekap seluruh transaksi pembelian / pengadaan bahan baku restoran</p>
            </div>
            <a href="{{ route('laporan.pengadaan.cetak', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-red-600 rounded-lg px-3 py-2 hover:bg-red-700 transition-colors">
                <i class="fa-solid fa-file-pdf"></i>
                Cetak PDF
            </a>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-xs font-medium text-gray-500">Total Biaya Pengadaan</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-xs font-medium text-gray-500">Total Transaksi</p>
                <p class="text-xl font-bold text-gray-900 mt-1">{{ $totalTransaksi }} kali</p>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <form action="{{ route('laporan.pengadaan') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 transition-all">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 transition-all">
                </div>
                <button type="submit" class="px-5 py-2 bg-gray-900 text-white rounded-lg text-sm font-semibold hover:bg-gray-800 transition-colors shrink-0">Terapkan Filter</button>
            </form>
        </div>

        {{-- Top Bahan Baku --}}
        @if($topBahan->count() > 0)
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50/50">
                <h3 class="font-semibold text-gray-900 text-sm">Bahan Baku Terbanyak Diadakan</h3>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($topBahan as $bahan)
                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center">{{ $loop->iteration }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 text-sm truncate">{{ $bahan->bahanBaku->nama_bahan ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ rtrim(rtrim(number_format($bahan->total_jumlah, 2, ',', '.'), '0'), ',') }} diadakan · Rp {{ number_format($bahan->total_biaya, 0, ',', '.') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Nomor PO & Tanggal</th>
                        <th class="px-4 py-3 text-left">Supplier</th>
                        <th class="px-4 py-3 text-left">Items</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-left">Pencatat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pengadaans as $i => $po)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium">{{ $pengadaans->firstItem() + $i }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900 font-mono text-xs">{{ $po->nomor_pengadaan }}</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $po->tanggal_pengadaan->format('d M Y') }}</p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $po->asal_pembelian ?: '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs text-gray-600">{{ $po->details->count() }} item</span>
                        </td>
                        <td class="px-4 py-3 text-right font-bold text-emerald-600">Rp {{ number_format($po->total_biaya, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $po->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center text-gray-400">
                            <i class="fa-solid fa-box-open text-4xl mb-3 text-gray-300 block"></i>
                            <p class="text-sm font-medium">Tidak ada data pengadaan pada periode ini.</p>
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
