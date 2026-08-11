<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f3f4f6; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; }
        .stats { margin-bottom: 20px; }
        .stats table { width: 50%; margin-top: 0; }
        .stats th { width: 50%; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PENJUALAN</h2>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <div class="stats">
        <table>
            <tr>
                <th>Total Transaksi</th>
                <td>{{ number_format($stats['totalTransaksi']) }}</td>
            </tr>
            <tr>
                <th>Total Pendapatan</th>
                <td>Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nomor Pesanan</th>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th>Jenis</th>
                <th>Status</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pesanans as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $p->id_pesanan }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_pesanan)->format('d/m/Y H:i') }}</td>
                <td>{{ optional($p->pelanggan)->nama ?? 'Umum' }}</td>
                <td>{{ optional($p->jenis_pesanan)->nama_jenis ?? '—' }}</td>
                <td>{{ $p->pembayaran->first()?->status_pembayaran?->nama_status ?? 'Belum Bayar' }}</td>
                <td class="text-right">Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
