<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Pendapatan Catering</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p { margin: 5px 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .total-row th { text-align: right; background-color: #eee; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Pendapatan Catering</h1>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tgl Acara</th>
                <th>Kode Pesanan</th>
                <th>Pelanggan</th>
                <th>Paket</th>
                <th class="text-right">Total Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pesanans as $index => $p)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_acara)->format('d/m/Y') }}</td>
                <td>{{ $p->kode_pesanan }}</td>
                <td>{{ $p->nama_pemesan }}</td>
                <td>{{ $p->paket->nama_paket ?? '-' }} ({{ $p->jumlah_porsi }} Porsi)</td>
                <td class="text-right">Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <th colspan="5">TOTAL PENDAPATAN CATERING</th>
                <th class="text-right">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
    <p style="text-align: right; margin-top: 30px;">
        Dicetak pada: {{ now()->format('d/m/Y H:i') }}
    </p>
</body>
</html>