<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Dapur #{{ $pesanan->no_pesanan }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 14px; color: #000; max-width: 300px; margin: 0 auto; padding: 10px; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .border-top { border-top: 2px dashed #000; padding-top: 5px; }
        .border-bottom { border-bottom: 2px dashed #000; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th { border-bottom: 1px solid #000; padding-bottom: 5px; text-align: left; }
        td { vertical-align: top; padding: 4px 0; border-bottom: 1px dotted #ccc; }
        @media print {
            body { max-width: 100%; padding: 0; }
        }
    </style>
    <style>
        .no-print { display: flex; justify-content: space-between; align-items: center; padding: 10px 20px; background-color: #f3f4f6; margin-bottom: 20px; border-bottom: 1px solid #e5e7eb; font-family: sans-serif; }
        .no-print button { padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
        .no-print button:hover { background-color: #1d4ed8; }
        .no-print a { color: #4b5563; text-decoration: none; font-weight: 500; }
        .no-print a:hover { color: #111827; }
        .receipt-container { margin: 0 auto; background: white; padding: 10px; border: 1px solid #ddd; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        @media print {
            .no-print { display: none !important; }
            .receipt-container { border: none; box-shadow: none; margin: 0; padding: 0; }
            body { background: white; }
        }
        body { background: #e5e7eb; margin: 0; }
    </style>
</head>
    <div class="no-print">
        <a href="javascript:window.close()">&larr; Tutup</a>
        <button onclick="window.print()">Cetak Struk</button>
    </div>
    <div class="receipt-container" style="max-width: 300px;">
    <div class="text-center mb-2 font-bold" style="font-size:18px;">
        *** STRUK DAPUR ***
    </div>
    
    <div class="border-top border-bottom mb-2 mt-2 font-bold">
        <table style="margin:0; font-size: 16px;">
            <tr><td style="width: 40%;">Meja</td><td>: {{ $pesanan->no_meja ?? '-' }}</td></tr>
            <tr><td>Jenis</td><td>: {{ strtoupper(str_replace('_', ' ', $pesanan->jenis_pesanan)) }}</td></tr>
            <tr><td>No</td><td>: {{ substr($pesanan->no_pesanan, -4) }}</td></tr>
            <tr><td style="font-size: 12px; font-weight: normal;">Waktu</td><td style="font-size: 12px; font-weight: normal;">: {{ $pesanan->created_at->format('H:i') }}</td></tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Qty</th>
                <th style="width: 85%;">Menu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pesanan->details as $detail)
            <tr>
                <td class="font-bold" style="font-size: 16px;">{{ $detail->jumlah }}x</td>
                <td>
                    <span class="font-bold" style="font-size: 16px;">{{ $detail->menu->nama }}</span>
                    @if($detail->catatan)
                    <br><span style="font-style: italic; font-size: 12px;">Catatan: {{ $detail->catatan }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</body>
</html>