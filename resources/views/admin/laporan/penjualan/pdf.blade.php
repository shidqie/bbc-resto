<!DOCTYPE html>
<html>
<head>
    <title>Laporan Penjualan</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap');
        * { font-family: 'Outfit', sans-serif !important; }
        body { font-family: 'Outfit', sans-serif !important; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0 0 5px 0; font-size: 18px; text-transform: uppercase; }
        .header h3 { margin: 0 0 5px 0; font-size: 14px; font-weight: normal; }
        .header p { margin: 0; font-size: 11px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 7px 10px; text-align: left; font-size: 11px; }
        th { background-color: #f3f4f6; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .stats-box { margin-bottom: 15px; border: 1px solid #ddd; padding: 10px; background-color: #f9fafb; }
        .stats-box td { border: none; padding: 4px 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>RUMAH MAKAN SAUNG BABAKAN CINTA</h2>
        <h3>LAPORAN PENJUALAN</h3>
        <p>Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <div class="stats-box">
        <table style="margin-top: 0;">
            <tr>
                <td style="width: 25%;"><strong>Total Pendapatan:</strong></td>
                <td style="width: 25%;">Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</td>
                <td style="width: 25%;"><strong>Jumlah Transaksi:</strong></td>
                <td style="width: 25%;">{{ number_format($stats['totalTransaksi']) }} Pesanan</td>
            </tr>
            <tr>
                <td><strong>Rata-rata Transaksi:</strong></td>
                <td colspan="3">Rp {{ number_format($stats['rataRataTransaksi'], 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 30px;">No</th>
                <th style="width: 80px;">Tanggal</th>
                <th style="width: 130px;">Kode Pesanan</th>
                <th style="width: 90px;">Jenis Pesanan</th>
                <th class="text-right" style="width: 110px;">Total Transaksi</th>
                <th class="text-center" style="width: 70px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanans as $index => $p)
            @php
                $jId = $p->jenis_pesanan_id;
                $jNama = $jId == 1 ? 'Dine-In' : ($jId == 2 ? 'Katering' : 'Nasi Box');
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($p->tanggal_pesanan)->format('d/m/Y') }}</td>
                <td>{{ $p->id_pesanan }}</td>
                <td>{{ $jNama }}</td>
                <td class="text-right">Rp {{ number_format($p->total_tagihan, 0, ',', '.') }}</td>
                <td class="text-center">Selesai</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Belum ada transaksi penjualan pada periode yang dipilih.</td>
            </tr>
            @endforelse
        </tbody>
        @if(count($pesanans) > 0)
        <tfoot>
            <tr style="font-weight: bold; background-color: #f9fafb;">
                <td colspan="4" class="text-right">Total Pendapatan:</td>
                <td class="text-right">Rp {{ number_format($stats['totalPendapatan'], 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>
</body>
</html>
