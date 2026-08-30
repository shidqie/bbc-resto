@extends('pdf.layout')

@section('title', 'Laporan Penjualan - RM Saung Babakan Cinta')

@section('content')
    <div class="doc-header">
        <div class="doc-title">LAPORAN PENJUALAN</div>
        <div class="doc-subtitle">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    <!-- Ringkasan KPI Table Formats -->
    <table class="pdf-table" style="width: 50%; margin-bottom: 14px;">
        <thead>
            <tr>
                <th style="width: 55%;">Keterangan</th>
                <th class="text-right" style="width: 45%;">Nilai</th>
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
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 23%;">ID Pesanan</th>
                <th class="text-center" style="width: 13%;">Tanggal</th>
                <th style="width: 12%;">Jenis</th>
                <th style="width: 22%;">Konsumen</th>
                <th class="text-right" style="width: 15%;">Total</th>
                <th class="text-center" style="width: 10%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanans as $index => $p)
            @php
                $jId = $p->jenis_pesanan_id;
                $jNama = $jId == 1 ? 'Dine-In' : ($jId == 2 ? 'Katering' : 'Nasi Box');
                $namaKonsumen = $p->nama_konsumen ?? ($p->pelanggan->nama ?? ($p->jadwal_pesanan->nama_penerima ?? 'Tamu'));
                if ($namaKonsumen === 'Tamu' && $p->meja) {
                    $nomorM = $p->meja->nomor_meja ?? '';
                    $namaKonsumen = str_starts_with(strtolower($nomorM), 'meja') ? $nomorM : 'Meja ' . $nomorM;
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono font-bold">{{ $p->id_pesanan }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($p->tanggal_pesanan)->format('d/m/Y') }}</td>
                <td>{{ $jNama }}</td>
                <td>{{ $namaKonsumen }}</td>
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
