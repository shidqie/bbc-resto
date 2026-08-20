<!DOCTYPE html>
<html>
<head>
    <title>Laporan Pengadaan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; }
        .header h3 { margin: 0 0 5px 0; font-size: 14px; font-weight: normal; }
        .header p { margin: 0; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 7px 10px; text-align: left; font-size: 11px; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RUMAH MAKAN SAUNG BABAKAN CINTA</h2>
        <h3>LAPORAN PENGADAAN</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 120px;">Kode Pengadaan</th>
                <th style="width: 80px;">Tanggal</th>
                <th>Supplier</th>
                <th class="text-right" style="width: 110px;">Jumlah Pembelian</th>
                <th class="text-center" style="width: 90px;">Status</th>
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
                    if ($harga <= 0) $harga = 15000;
                    $totalBeli += ($qty * $harga);
                }
                $totalSemua += $totalBeli;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $p->nomor_po }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_po)->format('d/m/Y') }}</td>
                <td>{{ $p->supplier ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($totalBeli, 0, ',', '.') }}</td>
                <td class="text-center">{{ ucfirst(str_replace('_', ' ', $p->status)) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada data pengadaan pada periode yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($pos) > 0)
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9fafb;">
                <td colspan="4" class="text-right">Total Pembelian:</td>
                <td class="text-right">Rp {{ number_format($totalSemua, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
