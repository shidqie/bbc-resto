{{-- Laporan Penjualan sesuai PRD --}}
@extends('layouts.pos')
@section('title', 'Laporan Penjualan')

@section('content')
<div class="flex-1 bg-gray-50 text-gray-800">
    <div class="w-full p-6 space-y-5">

        {{-- PAGE HEADER --}}
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="text-2xl font-black text-gray-900 tracking-tight">Laporan Penjualan</h1>
                <p class="text-sm text-gray-500 font-medium mt-1">Rekapitulasi transaksi penjualan (Dine In, Catering, Nasi Box) berdasarkan periode.</p>
            </div>
            <a href="{{ route('laporan.penjualan.cetak', array_merge(request()->query(), ['start_date' => $startDate, 'end_date' => $endDate, 'jenis' => $jenisPenjualan, 'status_pembayaran' => $statusPembayaran])) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-red-600 rounded-lg px-3 py-2 hover:bg-red-700 transition-colors">
                <x-heroicon-o-document class="w-4 h-4" />
                Cetak PDF
            </a>
        </div>

        {{-- FILTER BAR --}}
        <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
            <form action="{{ route('laporan.penjualan') }}" method="GET" class="flex flex-col md:flex-row gap-3 items-end flex-wrap">
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-sm font-semibold text-gray-500 mb-1">Tanggal Awal</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-sm font-semibold text-gray-500 mb-1">Tanggal Akhir</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-sm font-semibold text-gray-500 mb-1">Jenis Penjualan</label>
                    <select name="jenis" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                        <option value="">Semua</option>
                        <option value="dinein"   {{ $jenisPenjualan === 'dinein' ? 'selected' : '' }}>Dine In</option>
                        <option value="catering" {{ $jenisPenjualan === 'catering' ? 'selected' : '' }}>Catering</option>
                        <option value="nasibox"  {{ $jenisPenjualan === 'nasibox' ? 'selected' : '' }}>Nasi Box</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[140px]">
                    <label class="block text-sm font-semibold text-gray-500 mb-1">Status Pembayaran</label>
                    <select name="status_pembayaran" class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg bg-white outline-none focus:ring-1 focus:ring-blue-300 transition-all">
                        <option value="">Semua</option>
                        <option value="SEBAGIAN" {{ $statusPembayaran === 'SEBAGIAN' ? 'selected' : '' }}>DP</option>
                        <option value="LUNAS"    {{ $statusPembayaran === 'LUNAS' ? 'selected' : '' }}>Lunas</option>
                        <option value="MENUNGGU" {{ $statusPembayaran === 'MENUNGGU' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>
                <div class="flex gap-2 shrink-0">
                    <button type="submit" class="text-sm font-semibold text-white bg-gray-900 rounded-lg px-4 py-2 hover:bg-gray-800 transition-colors">Tampilkan</button>
                    <a href="{{ route('laporan.penjualan') }}" class="text-sm font-medium text-gray-600 border border-gray-200 bg-white rounded-lg px-4 py-2 hover:bg-gray-50 transition-colors">Reset</a>
                </div>
            </form>
        </div>

        {{-- STAT CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl border border-gray-200 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Total Transaksi</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($stats['totalTransaksi']) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-blue-100 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Total Penjualan</p>
                <p class="text-xl font-bold text-blue-600 mt-1">Rp {{ number_format($stats['totalPenjualan'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-emerald-100 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Pembayaran Masuk</p>
                <p class="text-xl font-bold text-emerald-600 mt-1">Rp {{ number_format($stats['totalDibayar'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-xl border border-amber-100 px-5 py-4">
                <p class="text-sm font-medium text-gray-500">Piutang</p>
                <p class="text-xl font-bold text-amber-600 mt-1">Rp {{ number_format($stats['totalPiutang'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- TABLE --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-sm font-semibold text-gray-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-left w-10">No.</th>
                        <th class="px-4 py-3 text-left">ID Pesanan</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Jenis</th>
                        <th class="px-4 py-3 text-left">Pelanggan / Meja</th>
                        <th class="px-4 py-3 text-right">Total Tagihan</th>
                        <th class="px-4 py-3 text-right">Total Dibayar</th>
                        <th class="px-4 py-3 text-left">Status Bayar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($pesanans as $i => $pesanan)
                    @php
                        $totalDibayarRow = $pesanan->pembayaran->sum('jumlah_bayar');
                        $statusBayar     = $pesanan->pembayaran->first()?->status_pembayaran?->nama_status ?? 'Menunggu';
                        $kodeBayar       = $pesanan->pembayaran->first()?->status_pembayaran?->kode_status ?? 'MENUNGGU';
                        $labelColor = match($kodeBayar) {
                            'LUNAS'    => 'bg-emerald-50 text-emerald-700',
                            'SEBAGIAN' => 'bg-amber-50 text-amber-700',
                            default    => 'bg-red-50 text-red-700',
                        };
                        $jenis      = $pesanan->jenis_pesanan?->kode_jenis ?? 'DIN';
                        $jenisLabel = $pesanan->jenis_pesanan?->nama_jenis ?? 'Dine In';
                        $jenisColor = match($jenis) {
                            'CAT' => 'bg-violet-50 text-violet-700',
                            'BOX' => 'bg-orange-50 text-orange-700',
                            default => 'bg-blue-50 text-blue-700',
                        };
                        $pelangganLabel = $pesanan->pelanggan?->nama
                            ?? ($pesanan->meja ? 'Meja ' . $pesanan->meja->nomor_meja : $pesanan->nomor_pesanan);
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-400 font-medium">{{ $pesanans->firstItem() + $i }}</td>
                        <td class="px-4 py-3 font-mono text-sm font-semibold text-gray-900">{{ $pesanan->nomor_pesanan ?? '#'.$pesanan->id }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <p>{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d M Y') }}</p>
                            <p class="text-gray-400">{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('H:i') }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block text-xs font-semibold px-2 py-0.5 rounded-xl {{ $jenisColor }}">{{ $jenisLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800">{{ $pelangganLabel }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-emerald-700">Rp {{ number_format($totalDibayarRow, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-xl {{ $labelColor }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $kodeBayar === 'LUNAS' ? 'bg-emerald-500' : ($kodeBayar === 'SEBAGIAN' ? 'bg-amber-500' : 'bg-red-500') }}"></span>
                                {{ $statusBayar }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state icon="document-text" title="Tidak ada transaksi" message="Tidak ada transaksi pada periode yang dipilih." :colspan="8" />
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $pesanans->links() }}</div>

    </div>
</div>
@endsection
