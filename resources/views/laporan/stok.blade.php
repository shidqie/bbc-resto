@extends('layouts.pos')

@section('title', 'Laporan Persediaan Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800 font-sans">
    <div class="w-full px-4 md:px-6 py-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-bold text-gray-900">Laporan Persediaan Bahan Baku</h1>
                <p class="text-xs text-gray-500 mt-0.5">Rekap harian mutasi &amp; pemakaian persediaan bahan baku restoran &amp; catering</p>
            </div>
            <a href="{{ route('laporan.stok.cetak', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-red-600 rounded-lg px-3 py-2 hover:bg-red-700 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Cetak PDF
            </a>
        </div>

        {{-- Filter --}}
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <form action="{{ route('laporan.stok') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end">
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>
                <div class="flex-1">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-gray-400 focus:border-gray-400 transition-all">
                </div>
                <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-4 py-2 hover:bg-gray-800 transition-colors shrink-0">Filter</button>
            </form>
        </div>

        {{-- TABEL 1: Rekap Penggunaan Harian --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Rekap Penggunaan Harian Stok</p>
                    <p class="text-xs text-gray-400 mt-0.5">Total bahan baku yang terpakai per tanggal transaksi</p>
                </div>
                <span class="text-xs font-semibold text-gray-700 bg-gray-100 rounded-full px-2.5 py-0.5">{{ count($penggunaanHarian) }} Catatan</span>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                        <th class="px-4 py-3 text-right">Total Penggunaan Harian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($penggunaanHarian as $i => $harian)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ \Carbon\Carbon::parse($harian->tanggal)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-700 font-medium">{{ $harian->bahanBaku->nama_bahan ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold text-red-600">
                            -{{ number_format($harian->total_penggunaan, 2, ',', '.') }}
                            <span class="text-xs text-gray-400 font-normal">{{ $harian->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-10 text-center text-xs text-gray-400">
                            Tidak ada catatan penggunaan harian stok pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- TABEL 2: Total Penggunaan per Bahan Baku --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">Total Penggunaan per Bahan Baku</p>
                <p class="text-xs text-gray-400 mt-0.5">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
            </div>
            <div class="p-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse($totalPenggunaanPerBahan as $totalBahan)
                <div class="flex items-center justify-between bg-gray-50 border border-gray-100 rounded-lg p-3">
                    <div>
                        <p class="text-xs font-semibold text-gray-900">{{ $totalBahan->bahanBaku->nama_bahan ?? '-' }}</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">{{ $totalBahan->bahanBaku->kategoriBahan->nama_kategori ?? 'Kategori' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-red-600">-{{ number_format($totalBahan->total_penggunaan, 2, ',', '.') }}</p>
                        <p class="text-[10px] text-gray-400">{{ $totalBahan->bahanBaku->satuan->nama_satuan ?? '' }}</p>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-8 text-center text-xs text-gray-400">
                    Tidak ada data akumulasi penggunaan stok pada periode ini.
                </div>
                @endforelse
            </div>
        </div>

        {{-- TABEL 3: Detail Mutasi --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
                <p class="text-sm font-semibold text-gray-900">Riwayat Detail Mutasi Stok</p>
                <p class="text-xs text-gray-400 mt-0.5">Semua catatan stok masuk dan keluar</p>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-12">No.</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-left">Bahan Baku</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($mutasis as $i => $mutasi)
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-500 font-medium">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $mutasi->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-900">{{ $mutasi->bahanBaku->nama_bahan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($mutasi->jenis_mutasi == 'masuk')
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700">Masuk</span>
                            @else
                                <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-md bg-red-50 text-red-700">Keluar</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-bold {{ $mutasi->jenis_mutasi == 'masuk' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $mutasi->jenis_mutasi == 'masuk' ? '+' : '' }}{{ number_format($mutasi->jumlah, 2, ',', '.') }}
                            <span class="text-xs text-gray-400 font-normal">{{ $mutasi->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate">{{ $mutasi->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-14 text-center text-gray-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                            <p class="text-sm font-medium">Tidak ada riwayat mutasi stok pada periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($mutasis->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $mutasis->links() }}</div>
            @endif
        </div>

    </div>
</div>
@endsection
