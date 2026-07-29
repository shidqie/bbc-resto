<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pengadaan #{{ $pengadaan->nomor_pengadaan }}</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.5; font-size: 14px; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #3B82F6; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #3B82F6; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 14px; }
        
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { vertical-align: top; }
        
        .title { font-weight: bold; font-size: 12px; color: #888; text-transform: uppercase; margin-bottom: 5px; }
        .content { margin-bottom: 15px; font-weight: bold; }
        
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .item-table th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 10px; text-align: left; font-size: 13px; }
        .item-table td { border-bottom: 1px solid #e2e8f0; padding: 10px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .summary { width: 300px; float: right; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 8px 0; }
        .summary-table .border-top { border-top: 2px solid #e2e8f0; font-weight: bold; font-size: 16px; color: #3B82F6; }
        
        .clearfix::after { content: ""; clear: both; display: table; }
        
        .footer { margin-top: 50px; text-align: center; color: #888; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1>SAUNG BABAKAN CINTA</h1>
                    <p>Jl. Raya Babakan Cinta No. 123, Kota Bandung</p>
                    <p>Telp: 0812-3456-7890</p>
                </td>
                <td style="text-align: right;">
                    <h2 style="margin:0; font-size: 24px; color:#1e293b;">BUKTI PENGADAAN</h2>
                    <p style="margin:5px 0 0; font-weight: bold;">#{{ $pengadaan->nomor_pengadaan }}</p>
                    <p style="margin:5px 0 0;">Tanggal: {{ $pengadaan->tanggal_pengadaan->format('d F Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <div class="title">Supplier:</div>
                <div class="content">
                    {{ $pengadaan->asal_pembelian ?: 'Tidak Ditentukan' }}
                </div>
            </td>
            <td style="width: 50%;">
                <div class="title">Detail Dokumen:</div>
                <div class="content" style="font-weight: normal;">
                    <strong>Pencatat:</strong> {{ $pengadaan->user->name ?? '-' }}<br>
                    <strong>Waktu Input:</strong> {{ $pengadaan->created_at->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 55%;">Bahan Baku</th>
                <th style="width: 40%;" class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pengadaan->details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    {{ $detail->bahanBaku->nama_bahan ?? 'Bahan Terhapus' }}<br>
                    <small style="color:#666;">Satuan: {{ $detail->satuan }}</small>
                </td>
                    <small style="color:#666;">Jumlah: {{ rtrim(rtrim(number_format($detail->jumlah, 2, ',', '.'), '0'), ',') }} {{ $detail->satuan }}</small>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($pengadaan->catatan)
    <div style="margin-top: 30px;">
        <div class="title">Catatan:</div>
        <div>{{ $pengadaan->catatan }}</div>
    </div>
    @endif

    <div class="footer">
        Dokumen ini dibuat secara otomatis oleh Sistem Saung Babakan Cinta.<br>
        Dicetak pada: {{ now()->format('d F Y H:i') }}
    </div>

</body>
</html>
