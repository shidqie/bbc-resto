@extends('pdf.layout')

@section('title', 'Laporan Pengadaan - RM Saung Babakan Cinta')

@section('content')
    <div class="doc-header">
        <div class="doc-title">LAPORAN PENGADAAN</div>
        <div class="doc-subtitle">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} &ndash; {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>
    </div>

    <table class="pdf-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">No</th>
                <th style="width: 25%;">Kode Pengadaan</th>
                <th class="text-center" style="width: 14%;">Tanggal</th>
                <th style="width: 24%;">Supplier</th>
                <th class="text-right" style="width: 18%;">Jumlah Pembelian</th>
                <th class="text-center" style="width: 14%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $totalSemua = 0; @endphp
            @forelse($pos as $index => $p)
            @php
                $totalBeli = 0;
                foreach ($p->detail_purchase_order as $d) {
                    $qty = (float) $d->jumlah_dipesan;
                    $harga = (float) $d->harga_satuan;
                    if ($harga <= 0) $harga = (float) optional($d->bahan_baku)->harga_satuan;
                    if ($harga <= 0) $harga = 16000;
                    $totalBeli += ($qty * $harga);
                }
                $totalSemua += $totalBeli;

                $st = strtolower($p->status);
                $statusLabel = 'Selesai';
                if (in_array($st, ['menunggu', 'draft', 'pending'])) {
                    $statusLabel = 'Menunggu barang';
                } elseif (in_array($st, ['sebagian', 'diterima_sebagian'])) {
                    $statusLabel = 'Diterima sebagian';
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-mono">{{ $p->nomor_po }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($p->tanggal_po)->format('d/m/Y') }}</td>
                <td>{{ $p->supplier ?? '-' }}</td>
                <td class="text-right font-mono">Rp {{ number_format($totalBeli, 0, ',', '.') }}</td>
                <td class="text-center">{{ $statusLabel }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada data pengadaan pada periode yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($pos) > 0)
        <tfoot>
            <tr class="font-bold">
                <td colspan="4" class="text-right">Total Pembelian:</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
@endsection
