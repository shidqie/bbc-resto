{{-- Laporan Persediaan Bahan Baku sesuai PRD --}}
@extends('layouts.pos')
@section('title', 'Laporan Persediaan Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <x-ui.page-header title="Laporan Persediaan Bahan Baku" subtitle="Aktivitas persediaan bahan baku (Operasional & Katering) berdasarkan periode." :breadcrumbs="['Laporan', 'Persediaan Bahan Baku']">
            <x-slot:actions>
                <a href="{{ route('laporan.stok.cetak', array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate])) }}"
                   target="_blank"
                   class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 bg-white border border-gray-200 rounded-lg px-3 py-2 hover:bg-gray-50 transition-colors shadow-sm">
                    <x-heroicon-o-document-text class="w-4 h-4 text-rose-500" />
                    Cetak PDF
                </a>
            </x-slot:actions>
        </x-ui.page-header>

        {{-- FILTER BAR --}}
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <form action="{{ route('laporan.stok') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end flex-wrap" x-data="{ jenis: '{{ $jenisPersediaan }}' }">
                <div class="flex-1 min-w-[130px]">
                    <label class="block text-sm font-semibold text-gray-500 mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                </div>
                <div class="flex-1 min-w-[130px]">
                    <label class="block text-sm font-semibold text-gray-500 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                </div>
                <x-ui.multi-select name="jenis_persediaan" :options="['' => 'Semua', 'OPERASIONAL' => 'Operasional', 'CATERING' => 'Katering']" :selected="request('jenis_persediaan', [])" label="Jenis Persediaan" type="radio" />
                <x-ui.multi-select name="kategori_id" :options="['' => 'Semua Kategori'] + $kategoris->pluck('nama_kategori', 'id')->all()" :selected="request('kategori_id', [])" label="Kategori Bahan" type="radio" />
                <x-ui.multi-select name="bahan_baku_id" :options="['' => 'Semua Bahan'] + $bahanBakus->pluck('nama_bahan', 'id')->all()" :selected="request('bahan_baku_id', [])" label="Bahan Baku" type="radio" />
                <div class="flex gap-2 shrink-0">
                    <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-4 py-2 hover:bg-gray-800 transition-colors">Tampilkan</button>
                    <a href="{{ route('laporan.stok') }}" class="text-sm font-medium text-gray-600 border border-gray-200 bg-white rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Reset</a>
                </div>
            </form>
        </div>

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Total Jenis Bahan</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_jenis'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-100 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Bahan Aman</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['total_aman'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-amber-100 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Stok Minimum</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['total_min'] }}</p>
            </div>
            <div class="bg-white rounded-xl border border-red-100 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Bahan Habis</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['total_habis'] }}</p>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <x-ui.table.header>
                    <th class="px-4 py-3.5 text-left w-10">No.</th>
                    <th class="px-4 py-3.5 text-left">Kode</th>
                    <th class="px-4 py-3.5 text-left">Nama Bahan</th>
                    <th class="px-4 py-3.5 text-right">Stok Awal</th>
                    <th class="px-4 py-3.5 text-right">Masuk</th>
                    <th class="px-4 py-3.5 text-right">Keluar</th>
                    <th class="px-4 py-3.5 text-right">Penyesuaian</th>
                    <th class="px-4 py-3.5 text-right">Stok Akhir</th>
                    <th class="px-4 py-3.5 text-left">Satuan</th>
                    <th class="px-4 py-3.5 text-left">Status</th>
                </x-ui.table.header>
                <tbody class="divide-y divide-gray-100">
                    @forelse($laporanBahan as $i => $item)
                    @php
                        $bahan = $item['bahan'];
                        $status = $item['status'];
                        $statusColor = match($status) {
                            'Aman'    => 'success',
                            'Minimum' => 'warning',
                            'Habis'   => 'danger',
                            default   => 'gray',
                        };
                    @endphp
                    <x-ui.table.row>
                        <td class="px-4 py-4 text-sm text-gray-400 font-medium">{{ $i + 1 }}</td>
                        <td class="px-4 py-4 font-mono text-sm font-semibold text-gray-500">{{ $bahan->kode_bahan ?? '-' }}</td>
                        <td class="px-4 py-4">
                            <p class="font-semibold text-gray-900">{{ $bahan->nama_bahan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $bahan->kategori_bahan_baku?->nama_kategori ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-4 text-right text-sm font-semibold text-gray-700">{{ number_format($item['stok_awal'], 2) }}</td>
                        <td class="px-4 py-4 text-right text-sm font-semibold text-emerald-600">+{{ number_format($item['stok_masuk'], 2) }}</td>
                        <td class="px-4 py-4 text-right text-sm font-semibold text-red-600">-{{ number_format($item['stok_keluar'], 2) }}</td>
                        <td class="px-4 py-4 text-right text-sm font-semibold text-blue-600">
                            {{ $item['penyesuaian'] >= 0 ? '+' : '' }}{{ number_format($item['penyesuaian'], 2) }}
                        </td>
                        <td class="px-4 py-4 text-right">
                            <span class="font-bold text-gray-900">{{ number_format($item['stok_akhir'], 2) }}</span>
                        </td>
                        <td class="px-4 py-4 text-sm text-gray-500">{{ $bahan->satuan?->singkatan ?? '-' }}</td>
                        <td class="px-4 py-4">
                            <x-ui.badge :color="$statusColor" size="sm" dot>{{ $status }}</x-ui.badge>
                        </td>
                    </x-ui.table.row>
                    @empty
                    <x-empty-state icon="archive-box" title="Tidak terdapat data" message="Pastikan sudah ada transaksi penerimaan atau penggunaan bahan baku." :colspan="10" />
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
