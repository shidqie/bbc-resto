<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengadaan Bahan Baku</title>
    <style>
        body { font-family: sans-serif; color: #333; font-size: 13px; margin: 0; padding: 24px; }
        h1 { margin: 0; font-size: 20px; color: #1e293b; }
        .subtitle { color: #64748b; font-size: 12px; margin-top: 4px; }
        .header-table { width: 100%; margin-bottom: 24px; border-bottom: 2px solid #3B82F6; padding-bottom: 12px; }
        .stat-row { width: 100%; margin-bottom: 20px; }
        .stat-box { display: inline-block; width: 48%; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; box-sizing: border-box; }
        .stat-label { font-size: 11px; color: #64748b; text-transform: uppercase; }
        .stat-value { font-size: 18px; font-weight: bold; color: #1e293b; margin-top: 4px; }
        .stat-value.green { color: #16a34a; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        thead th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 8px 10px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
        tbody tr:hover td { background: #f8fafc; }
        .text-right { text-align: right; }
        tfoot td { padding: 10px; font-weight: bold; border-top: 2px solid #e2e8f0; background: #f8fafc; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td>
                <h1>SAUNG BABAKAN CINTA</h1>
                <p class="subtitle">Jl. Raya Babakan Cinta No. 123, Kota Bandung</p>
            </td>
            <td style="text-align: right;">
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">LAPORAN PENGADAAN</h2>
                <p class="subtitle">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                <p class="subtitle">Dicetak: {{ now()->format('d M Y H:i') }}</p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%; padding-right: 8px;">
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Total Biaya Pengadaan</div>
                    <div style="font-size: 18px; font-weight: bold; color: #16a34a; margin-top: 4px;">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 8px;">
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Total Transaksi</div>
                    <div style="font-size: 18px; font-weight: bold; color: #1e293b; margin-top: 4px;">{{ $pengadaans->count() }} Pengadaan</div>
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nomor PO</th>
                <th>Tanggal</th>
                <th>Supplier</th>
                <th>Items</th>
                <th style="text-align: right;">Total Biaya</th>
                <th>Pencatat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengadaans as $i => $po)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td><strong style="font-family: monospace;">{{ $po->nomor_pengadaan }}</strong></td>
                <td>{{ $po->tanggal_pengadaan->format('d/m/Y') }}</td>
                <td>{{ $po->asal_pembelian ?: '-' }}</td>
                <td>{{ $po->details->count() }} item</td>
                <td style="text-align: right; color: #16a34a; font-weight: bold;">Rp {{ number_format($po->total_pengadaan, 0, ',', '.') }}</td>
                <td>{{ $po->diajukan_oleh_pengguna->nama ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; color: #94a3b8; padding: 24px;">Tidak ada data pengadaan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;">TOTAL:</td>
                <td style="text-align: right; color: #16a34a;">Rp {{ number_format($totalBiaya, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Sistem Informasi Saung Babakan Cinta.
    </div>
</body>
</html>
