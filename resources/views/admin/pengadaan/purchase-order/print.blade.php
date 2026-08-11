<!DOCTYPE html>
<html>
<head>
    <title>Purchase Order {{ $po->nomor_po }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; }
        .header { text-align: center; margin-bottom: 6px; }
        .header h2 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; font-size: 11px; color: #555; }
        .title { text-align: center; margin: 16px 0; }
        .title h3 { margin: 0; font-size: 15px; text-transform: uppercase; letter-spacing: 1px; }
        .info { margin: 14px 0; font-size: 12px; }
        .info td { padding: 2px 8px 2px 0; }
        .info .label { font-weight: bold; width: 150px; }
        table.detail { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.detail th, table.detail td { border: 1px solid #999; padding: 7px 8px; text-align: left; }
        table.detail th { background-color: #eef0f2; font-size: 11px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer { margin-top: 24px; font-size: 10px; color: #777; text-align: center; }
        .sign { margin-top: 40px; width: 100%; }
        .sign td { width: 50%; text-align: center; font-size: 11px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Rumah Makan Saung Babakan Cinta</h2>
    </div>

    <div class="title">
        <h3>Purchase Order</h3>
    </div>

    <table class="info">
        <tr><td class="label">Kode PO</td><td>: {{ $po->nomor_po }}</td></tr>
        <tr><td class="label">Tanggal PO</td><td>: {{ \Carbon\Carbon::parse($po->tanggal_po)->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Supplier/Toko</td><td>: {{ $po->supplier }}</td></tr>
        <tr><td class="label">Kode Permintaan</td><td>: {{ optional($po->pengadaan_bahan)->id_pengadaan ?? '-' }}</td></tr>
        @if($po->catatan)
        <tr><td class="label">Catatan</td><td>: {{ $po->catatan }}</td></tr>
        @endif
    </table>

    <table class="detail">
        <thead>
            <tr>
                <th class="text-center" style="width:30px;">No</th>
                <th>Bahan Baku</th>
                <th class="text-center">Jumlah</th>
                <th class="text-center">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($po->detail_purchase_order as $i => $d)
            <tr>
                <td class="text-center">{{ $i + 1 }}</td>
                <td>{{ optional($d->bahan_baku)->nama_bahan ?? '-' }}</td>
                <td class="text-center">{{ (float) $d->jumlah_dipesan }}</td>
                <td class="text-center">{{ optional(optional($d->bahan_baku)->satuan)->nama_satuan ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sign">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div style="margin-top: 70px;">({{ optional($po->pengadaan_bahan?->diajukan_oleh_pengguna)->nama ?? '________________' }})</div>
            </td>
            <td>
                <div>Dibuat Oleh,</div>
                <div style="margin-top: 70px;">({{ optional($po->dibuat_oleh_pengguna)->nama ?? '________________' }})</div>
            </td>
        </tr>
    </table>

    <div class="footer">Dokumen ini dibuat otomatis oleh sistem BBC Resto — {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>