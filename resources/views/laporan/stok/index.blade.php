{{-- Laporan Persediaan Bahan Baku sesuai PRD --}}
@extends('layouts.pos')
@section('title', 'Laporan Persediaan Bahan Baku')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Laporan Persediaan Bahan Baku</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Aktivitas persediaan bahan baku (Operasional & Catering) berdasarkan periode.</p>
            </div>
            <a href="{{ route('laporan.stok.cetak', array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate])) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-red-600 rounded-2xl px-3 py-2 hover:bg-red-700 transition-colors">
                <x-heroicon-o-document class="w-4 h-4" />
                Cetak PDF
            </a>
        </div>

        {{-- FILTER BAR --}}
        <div class="bg-white rounded-3xl border border-gray-200 px-5 py-4">
            <form action="{{ route('laporan.stok') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end flex-wrap" x-data="{ jenis: '{{ $jenisPersediaan }}' }">
                <div class="flex-1 min-w-[130px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                </div>
                <div class="flex-1 min-w-[130px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                </div>
                <div class="flex-1 min-w-[130px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Jenis Persediaan</label>
                    <select name="jenis_persediaan" x-model="jenis" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl bg-white outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                        <option value="">Semua</option>
                        <option value="OPERASIONAL" {{ $jenisPersediaan === 'OPERASIONAL' ? 'selected' : '' }}>Operasional</option>
                        <option value="CATERING"    {{ $jenisPersediaan === 'CATERING' ? 'selected' : '' }}>Catering</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[130px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Kategori Bahan</label>
                    <select name="kategori_id" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl bg-white outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ $kategoriId == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[130px]">
                    <label class="block text-xs font-semibold text-gray-500 mb-1">Bahan Baku</label>
                    <select name="bahan_baku_id" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-2xl bg-white outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                        <option value="">Semua Bahan</option>
                        @foreach($bahanBakus as $bb)
                        <option value="{{ $bb->id }}" {{ $bahanBakuId == $bb->id ? 'selected' : '' }}>{{ $bb->nama_bahan }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-2xl px-4 py-2 hover:bg-gray-800 transition-colors">Tampilkan</button>
                    <a href="{{ route('laporan.stok') }}" class="text-sm font-medium text-gray-600 border border-gray-200 bg-white rounded-2xl px-4 py-2 hover:bg-gray-50 transition-colors">Reset</a>
                </div>
            </form>
        </div>

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-3xl border border-gray-200 px-5 py-4">
                <p class="text-xs font-medium text-gray-500">Total Jenis Bahan</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_jenis'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-emerald-100 px-5 py-4">
                <p class="text-xs font-medium text-gray-500">Bahan Aman</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['total_aman'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-amber-100 px-5 py-4">
                <p class="text-xs font-medium text-gray-500">Stok Minimum</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['total_min'] }}</p>
            </div>
            <div class="bg-white rounded-3xl border border-red-100 px-5 py-4">
                <p class="text-xs font-medium text-gray-500">Bahan Habis</p>
                <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['total_habis'] }}</p>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-3xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-10">No.</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Bahan</th>
                        <th class="px-4 py-3 text-right">Stok Awal</th>
                        <th class="px-4 py-3 text-right">Masuk</th>
                        <th class="px-4 py-3 text-right">Keluar</th>
                        <th class="px-4 py-3 text-right">Penyesuaian</th>
                        <th class="px-4 py-3 text-right">Stok Akhir</th>
                        <th class="px-4 py-3 text-left">Satuan</th>
                        <th class="px-4 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($laporanBahan as $i => $item)
                    @php
                        $bahan = $item['bahan'];
                        $status = $item['status'];
                        $statusColor = match($status) {
                            'Aman'    => 'bg-emerald-50 text-emerald-700',
                            'Minimum' => 'bg-amber-50 text-amber-700',
                            'Habis'   => 'bg-red-50 text-red-700',
                            default   => 'bg-gray-100 text-gray-500',
                        };
                        $statusDot = match($status) {
                            'Aman'    => 'bg-emerald-500',
                            'Minimum' => 'bg-amber-500',
                            'Habis'   => 'bg-red-500',
                            default   => 'bg-gray-400',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-xs text-gray-400 font-medium">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-mono text-xs font-semibold text-gray-500">{{ $bahan->kode_bahan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <p class="font-semibold text-gray-900">{{ $bahan->nama_bahan }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $bahan->kategori_bahan_baku?->nama_kategori ?? '-' }}</p>
                        </td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-gray-700">{{ number_format($item['stok_awal'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-emerald-600">+{{ number_format($item['stok_masuk'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-red-600">-{{ number_format($item['stok_keluar'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-blue-600">
                            {{ $item['penyesuaian'] >= 0 ? '+' : '' }}{{ number_format($item['penyesuaian'], 2) }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-bold text-gray-900">{{ number_format($item['stok_akhir'], 2) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-500">{{ $bahan->satuan?->singkatan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl {{ $statusColor }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $statusDot }}"></span>{{ $status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-16 text-center text-gray-400">
                            <x-heroicon-o-archive-box class="w-12 h-12 mb-3 text-gray-300" />
                            <p class="text-sm font-medium">Tidak terdapat data pada periode yang dipilih.</p>
                            <p class="text-xs text-gray-400 mt-1">Pastikan sudah ada transaksi penerimaan atau penggunaan bahan baku.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
