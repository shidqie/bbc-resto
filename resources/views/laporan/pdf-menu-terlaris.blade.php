<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Menu Terlaris</title>
    <style>
        body { font-family: sans-serif; color: #333; font-size: 13px; margin: 0; padding: 24px; }
        h1 { margin: 0; font-size: 20px; color: #1e293b; }
        .subtitle { color: #64748b; font-size: 12px; margin-top: 4px; }
        .header-table { width: 100%; margin-bottom: 24px; border-bottom: 2px solid #3B82F6; padding-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        thead th { background: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 8px 10px; text-align: left; font-size: 11px; color: #64748b; text-transform: uppercase; }
        tbody td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; font-size: 12px; }
        .text-right { text-align: right; }
        tfoot td { padding: 10px; font-weight: bold; border-top: 2px solid #e2e8f0; background: #f8fafc; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        .rank-1 { background: #fef9c3; }
        .rank-2 { background: #f8fafc; }
        .rank-3 { background: #fef3c7; }
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
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">LAPORAN MENU TERLARIS</h2>
                <p class="subtitle">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
                <p class="subtitle">Dicetak: {{ now()->format('d M Y H:i') }}</p>
            </td>
        </tr>
    </table>

    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <td style="width: 50%; padding-right: 8px;">
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Total Pendapatan</div>
                    <div style="font-size: 18px; font-weight: bold; color: #16a34a; margin-top: 4px;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 8px;">
                <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px;">
                    <div style="font-size: 11px; color: #64748b; text-transform: uppercase;">Total Item Terjual</div>
                    <div style="font-size: 18px; font-weight: bold; color: #1e293b; margin-top: 4px;">{{ number_format($totalTerjual, 0, ',', '.') }} porsi</div>
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 40px;">Rank</th>
                <th>Nama Menu</th>
                <th style="text-align: right;">Harga Satuan</th>
                <th style="text-align: right;">Total Terjual</th>
                <th style="text-align: right;">Total Pendapatan</th>
                <th style="text-align: right;">% Kontribusi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($menuTerlaris as $i => $menu)
            <tr class="{{ $i === 0 ? 'rank-1' : ($i === 1 ? 'rank-2' : ($i === 2 ? 'rank-3' : '')) }}">
                <td style="text-align: center; font-weight: bold;">
                    @if($i === 0) 🥇 @elseif($i === 1) 🥈 @elseif($i === 2) 🥉 @else {{ $i + 1 }} @endif
                </td>
                <td><strong>{{ $menu->nama }}</strong></td>
                <td style="text-align: right;">Rp {{ number_format($menu->harga, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($menu->total_qty, 0, ',', '.') }} porsi</td>
                <td style="text-align: right; font-weight: bold; color: #16a34a;">Rp {{ number_format($menu->total_pendapatan, 0, ',', '.') }}</td>
                <td style="text-align: right; color: #64748b;">
                    {{ $totalTerjual > 0 ? number_format(($menu->total_qty / $totalTerjual) * 100, 1) : 0 }}%
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">Belum ada data penjualan pada periode ini.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;">TOTAL:</td>
                <td style="text-align: right;">{{ number_format($totalTerjual, 0, ',', '.') }} porsi</td>
                <td style="text-align: right; color: #16a34a;">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                <td style="text-align: right;">100%</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh Sistem Informasi Saung Babakan Cinta.
    </div>
</body>
</html>
