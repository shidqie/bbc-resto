@extends('pdf.layout')

@section('title', 'Laporan Penjualan - RM Saung Babakan Cinta')

@section('content')
    <div class="doc-header">
        <div class="company-name">RUMAH MAKAN SAUNG BABAKAN CINTA</div>
        <div class="doc-title">LAPORAN PENJUALAN</div>
        <div class="doc-subtitle">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
        <div class="header-divider"></div>
    </div>

    <!-- Ringkasan KPI Table Formats -->
    <table class="pdf-table" style="width: 55%; margin-bottom: 18px;">
        <thead>
            <tr>
                <th>Keterangan</th>
                <th class="text-right">Nilai</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Pendapatan</strong></td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Jumlah Transaksi</strong></td>
                <td class="text-right font-bold">{{ number_format($stats['totalTransaksi']) }} Transaksi</td>
            </tr>
            <tr>
                <td><strong>Rata-rata Transaksi</strong></td>
                <td class="text-right font-mono">Rp {{ number_format($stats['rataRataTransaksi'], 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <table class="pdf-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">No</th>
                <th style="width: 140px;">Kode Pesanan</th>
                <th class="text-center" style="width: 95px;">Tanggal</th>
                <th style="width: 90px;">Jenis</th>
                <th>Pelanggan</th>
                <th class="text-right" style="width: 120px;">Total</th>
                <th class="text-center" style="width: 80px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanans as $index => $p)
            @php
                $jId = $p->jenis_pesanan_id;
                $jNama = $jId == 1 ? 'Dine-In' : ($jId == 2 ? 'Katering' : 'Nasi Box');
                $pelangganNama = $jId == 1 ? ('Meja ' . (optional($p->meja)->nomor_meja ?? '-')) : (optional($p->pelanggan)->nama ?? 'Umum');
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono font-bold">{{ $p->id_pesanan }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($p->tanggal_pesanan)->format('d/m/Y') }}</td>
                <td>{{ $jNama }}</td>
                <td>{{ $pelangganNama }}</td>
                <td class="text-right font-mono">Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}</td>
                <td class="text-center">Selesai</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada transaksi penjualan pada periode yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($pesanans) > 0)
        <tfoot>
            <tr class="font-bold">
                <td colspan="5" class="text-right">Total Pendapatan:</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
@endsection
