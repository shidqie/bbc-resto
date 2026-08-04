@extends('layouts.pos')

@section('content')
<div class="flex-1 overflow-auto bg-gray-50/50 text-gray-800 font-sans">
    <div class="w-full p-6 ] space-y-6">
        
        <x-ui.page-header title="Laporan Pendapatan Catering" subtitle="Ringkasan transaksi catering yang telah selesai">
            <x-slot:actions>
                <x-ui.button href="{{ route('laporan.catering.cetak', ['start_date' => $startDate, 'end_date' => $endDate]) }}" icon="printer" variant="outline">Cetak PDF</x-ui.button>
            </x-slot:actions>
        </x-ui.page-header>

        <form action="{{ route('laporan.catering') }}" method="GET" class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dari Tanggal (Acara)</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all bg-white">
            </div>
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Sampai Tanggal (Acara)</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-4 py-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-[#3B82F6] outline-none text-sm transition-all bg-white">
            </div>
            <x-ui.button type="submit" icon="sparkles">Filter Data</x-ui.button>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-ui.stat-card label="Total Pendapatan (Catering)" value="Rp {{ number_format($totalPendapatan, 0, ',', '.') }}" icon="wallet" color="green" />
            <x-ui.stat-card label="Total Transaksi Selesai" :value="$totalTransaksi" icon="chart-bar" color="blue" />
        </div>

        <x-ui.data-table>
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-white text-gray-500 text-sm uppercase tracking-wider border-b border-gray-100">
                        <th class="px-6 py-4 font-semibold">Tgl Acara</th>
                        <th class="px-6 py-4 font-semibold">Kode Pesanan</th>
                        <th class="px-6 py-4 font-semibold">Pelanggan</th>
                        <th class="px-6 py-4 font-semibold">Paket</th>
                        <th class="px-6 py-4 font-semibold text-right">Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    @forelse($pesanans as $p)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 font-medium">{{ \Carbon\Carbon::parse($p->tanggal_acara)->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ $p->kode_pesanan }}</td>
                            <td class="px-6 py-4 text-gray-900 font-medium">{{ $p->nama_pemesan }}</td>
                            <td class="px-6 py-4">
                                <span class="text-[#3B82F6]">{{ $p->paket->nama_paket ?? 'Paket Catering' }}</span> ({{ $p->jumlah_porsi }} Porsi)
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state icon="sparkles" title="Tidak ada data catering" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($pesanans->count() > 0)
                    <tfoot>
                        <tr class="bg-gray-50 border-t border-gray-200">
                            <td colspan="4" class="px-6 py-4 font-bold text-right text-gray-900">Total Pendapatan:</td>
                            <td class="px-6 py-4 font-bold text-right text-green-600">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </x-ui.data-table>
    </div>
</div>
@endsection