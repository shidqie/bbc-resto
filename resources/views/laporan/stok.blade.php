@extends('layouts.pos')

@section('title', 'Laporan Persediaan Bahan Baku')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Laporan Persediaan Bahan Baku</h2>
                <p class="text-sm text-gray-500 mt-1">Rekap harian mutasi & pemakaian persediaan bahan baku restoran & catering</p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <form action="{{ route('laporan.stok') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                    <input type="date" name="start_date" id="start_date" value="{{ $startDate }}" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-sm text-sm">
                </div>
                <div class="flex-1 w-full">
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" id="end_date" value="{{ $endDate }}" class="w-full rounded-lg border-gray-300 focus:border-primary focus:ring-primary shadow-sm text-sm">
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <button type="submit" class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-[#0F2E23] hover:bg-[#0a1f17] text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                        <x-heroicon-o-funnel class="mr-2 w-4 h-4 inline-block shrink-0" /> Filter
                    </button>
                    <a href="{{ route('laporan.stok.cetak', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-sm font-bold rounded-xl transition-colors shadow-sm">
                        <x-heroicon-o-document-text class="mr-2 w-4 h-4 inline-block shrink-0" /> Cetak PDF
                    </a>
                </div>
            </form>
        </div>

        <!-- ── TABEL 1: REKAP PENGGUNAAN HARIAN STOK ── -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-emerald-50/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-[#0F2E23] flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-emerald-700" />
                        Rekap Penggunaan Harian Stok
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Total bahan baku yang terpakai per tanggal transaksi</p>
                </div>
                <span class="text-xs font-bold text-emerald-800 bg-emerald-100 px-3 py-1 rounded-full border border-emerald-200">
                    {{ count($penggunaanHarian) }} Catatan Harian
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Penggunaan Harian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($penggunaanHarian as $harian)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-3.5 whitespace-nowrap text-sm font-bold text-gray-900">
                                {{ \Carbon\Carbon::parse($harian->tanggal)->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-sm font-semibold text-gray-900">
                                {{ $harian->bahanBaku->nama_bahan ?? '-' }}
                            </td>
                            <td class="px-6 py-3.5 whitespace-nowrap text-sm font-bold text-right text-red-600">
                                -{{ number_format($harian->total_penggunaan, 2, ',', '.') }} {{ $harian->bahanBaku->satuan->nama_satuan ?? '' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500 text-sm">
                                Tidak ada catatan penggunaan harian stok pada periode ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ── TABEL 2: AKUMULASI PENGGUNAAN PER BAHAN BAKU ── -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-amber-50/40 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-amber-950 flex items-center gap-2">
                        <x-heroicon-o-fire class="w-5 h-5 text-amber-700" />
                        Total Penggunaan per Bahan Baku (Periode Ini)
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Akumulasi total pemakaian bahan baku dari {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                </div>
            </div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($totalPenggunaanPerBahan as $totalBahan)
                <div class="bg-gray-50 border border-gray-200/80 rounded-xl p-4 flex items-center justify-between">
                    <div>
                        <p class="text-sm font-bold text-gray-900">{{ $totalBahan->bahanBaku->nama_bahan ?? '-' }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Stok Saat Ini: <span class="font-bold text-gray-700">{{ number_format($totalBahan->bahanBaku->stok ?? 0, 2, ',', '.') }} {{ $totalBahan->bahanBaku->satuan->nama_satuan ?? '' }}</span></p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-gray-400 block uppercase">Total Terpakai</span>
                        <span class="text-base font-black text-red-600">-{{ number_format($totalBahan->total_penggunaan, 2, ',', '.') }} {{ $totalBahan->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-4 text-center text-gray-500 text-sm">
                    Belum ada penggunaan bahan baku pada periode ini.
                </div>
                @endforelse
            </div>
        </div>

        <!-- ── TABEL 3: DETAIL LOG RIWAYAT MUTASI STOK ── -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-base font-bold text-gray-900">Riwayat Detail Mutasi Stok</h3>
                <span class="text-xs font-semibold text-gray-500">{{ count($mutasis) }} Transaksi Log</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Bahan Baku</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Transaksi</th>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Jumlah Mutasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($mutasis as $mutasi)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $mutasi->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $mutasi->bahanBaku->nama_bahan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($mutasi->jenis_mutasi === 'masuk')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border bg-emerald-50 text-emerald-800 border-emerald-200">
                                        Masuk
                                    </span>
                                @elseif($mutasi->jenis_mutasi === 'keluar')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border bg-red-50 text-red-800 border-red-200">
                                        Keluar
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border bg-amber-50 text-amber-800 border-amber-200">
                                        Penyesuaian
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $mutasi->keterangan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-right">
                                @if($mutasi->jenis_mutasi === 'masuk' || $mutasi->jumlah > 0)
                                    <span class="text-emerald-700">+{{ $mutasi->jumlah }} {{ $mutasi->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                                @else
                                    <span class="text-red-600">{{ $mutasi->jumlah }} {{ $mutasi->bahanBaku->satuan->nama_satuan ?? '' }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-gray-500">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <x-heroicon-o-archive-box class="text-2xl text-gray-400 w-full h-full max-w-[1em] max-h-[1em] inline-block" />
                                </div>
                                <h3 class="text-sm font-medium text-gray-900 mb-1">Tidak ada mutasi</h3>
                                <p class="text-sm text-gray-500">Tidak ada mutasi stok pada periode ini.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
