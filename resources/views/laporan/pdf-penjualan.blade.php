<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 5px 0;
            color: #EA580C;
        }
        .header p {
            margin: 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary {
            width: 300px;
            float: right;
        }
        .summary table th {
            background-color: transparent;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>SAUNG BABAKAN CINTA (BBC RESTO)</h2>
        <p>Laporan Penjualan Keseluruhan</p>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Tanggal</th>
                <th width="15%">No. Pesanan</th>
                <th width="15%">Kasir</th>
                <th width="15%">Metode</th>
                <th width="35%" class="text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanans as $index => $pesanan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ $pesanan->created_at->format('d/m/Y H:i') }}</td>
                <td>#{{ str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</td>
                <td>{{ $pesanan->user->name ?? 'Kasir' }}</td>
                <td>{{ strtoupper($pesanan->metode_pembayaran) }}</td>
                <td class="text-right">{{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data penjualan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <table>
            <tr>
                <th>Total Transaksi:</th>
                <td class="text-right">{{ count($pesanans) }}</td>
            </tr>
            <tr>
                <th>Total Pendapatan:</th>
                <td class="text-right"><strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong></td>
            </tr>
        </table>
    </div>
</body>
</html>
