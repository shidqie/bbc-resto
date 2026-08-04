<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PO {{ $pengadaan->nomor_pengadaan }}</title>
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
        .info-grid { width: 100%; margin-top: 8px; }
        .info-label { color: #64748b; font-size: 11px; text-transform: uppercase; }
        .info-value { font-size: 13px; font-weight: bold; color: #1e293b; margin-top: 2px; }
        .footer { margin-top: 40px; text-align: center; color: #94a3b8; font-size: 11px; border-top: 1px solid #e2e8f0; padding-top: 16px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; background: #dbeafe; color: #1d4ed8; }
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
                <h2 style="margin: 0; font-size: 18px; color: #1e293b;">PURCHASE ORDER</h2>
                <p class="subtitle">{{ $pengadaan->nomor_pengadaan }}</p>
                <p class="subtitle">Dicetak: {{ now()->format('d M Y H:i') }}</p>
            </td>
        </tr>
    </table>

    <table class="info-grid">
        <tr>
            <td style="width: 33%;">
                <div class="info-label">Tanggal PO</div>
                <div class="info-value">{{ $pengadaan->tanggal_pengadaan?->format('d M Y') }}</div>
            </td>
            <td style="width: 33%;">
                <div class="info-label">Perkiraan Datang</div>
                <div class="info-value">{{ $pengadaan->perkiraan_tanggal_datang?->format('d M Y') ?? '-' }}</div>
            </td>
            <td style="width: 33%;">
                <div class="info-label">Jenis</div>
                <div class="info-value">{{ $pengadaan->jenis_pengadaan }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="info-label">Pemasok</div>
                <div class="info-value">{{ $pengadaan->pemasok?->nama_pemasok ?? $pengadaan->nama_pemasok ?? '-' }}</div>
            </td>
            <td>
                <div class="info-label">Status</div>
                <div class="info-value"><span class="badge">{{ $pengadaan->status_pengadaan?->nama_status ?? '-' }}</span></div>
            </td>
            <td>
                <div class="info-label">Pencatat</div>
                <div class="info-value">{{ $pengadaan->diajukan_oleh_pengguna?->nama ?? '-' }}</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Bahan Baku</th>
                <th style="text-align: right;">Jumlah</th>
                <th>Satuan</th>
                <th style="text-align: right;">Harga Satuan</th>
                <th style="text-align: right;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pengadaan->detail_pengadaan_bahan as $i => $d)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $d->bahan_baku?->nama_bahan ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($d->jumlah_dipesan, 3, ',', '.') }}</td>
                <td>{{ $d->satuan?->nama_satuan ?? $d->satuan?->singkatan ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                <td style="text-align: right; font-weight: bold;">{{ number_format($d->subtotal, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: #94a3b8; padding: 24px;">Tidak ada item pengadaan.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right;">TOTAL:</td>
                <td style="text-align: right; color: #16a34a;">Rp {{ number_format($pengadaan->total_pengadaan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    @if($pengadaan->catatan)
    <p style="margin-top: 16px; font-size: 12px; color: #64748b;"><strong>Catatan:</strong> {{ $pengadaan->catatan }}</p>
    @endif

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh Sistem Informasi Saung Babakan Cinta.
    </div>
</body>
</html>
