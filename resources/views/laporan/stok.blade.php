@extends('layouts.pos')

@section('title', 'Laporan Stok')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="p-4 md:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold text-gray-900">Laporan Mutasi & Penggunaan Stok</h2>
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
                    <button type="submit" class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2 bg-primary hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                        <x-heroicon-o-funnel class="mr-2 w-5 h-5 inline-block shrink-0" /> Filter
                    </button>
                    <a href="{{ route('laporan.stok.cetak', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="flex-1 md:flex-none inline-flex justify-center items-center px-4 py-2 bg-danger hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors shadow-sm">
                        <x-heroicon-o-document-text class="mr-2 w-5 h-5 inline-block shrink-0" /> Cetak PDF
                    </a>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
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
                                {{ $mutasi->bahanBaku->nama_bahan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($mutasi->jenis_mutasi === 'masuk')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border bg-emerald-50 text-emerald-700 border-emerald-200">
                                        Masuk
                                    </span>
                                @elseif($mutasi->jenis_mutasi === 'keluar')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border bg-red-50 text-red-700 border-red-200">
                                        Keluar
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border bg-yellow-50 text-yellow-700 border-yellow-200">
                                        Penyesuaian
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $mutasi->keterangan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-right">
                                @if($mutasi->jenis_mutasi === 'masuk' || $mutasi->jumlah > 0)
                                    <span class="text-emerald-600">+{{ $mutasi->jumlah }} {{ $mutasi->bahanBaku->satuan->nama_satuan }}</span>
                                @else
                                    <span class="text-red-600">{{ $mutasi->jumlah }} {{ $mutasi->bahanBaku->satuan->nama_satuan }}</span>
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
