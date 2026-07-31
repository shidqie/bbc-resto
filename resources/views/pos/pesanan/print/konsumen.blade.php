<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Konsumen #{{ $pesanan->no_pesanan }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; max-width: 300px; margin: 0 auto; padding: 10px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 8px; }
        .mt-2 { margin-top: 8px; }
        .border-top { border-top: 1px dashed #000; padding-top: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td { vertical-align: top; padding: 2px 0; }
        .item-name { padding-bottom: 2px; }
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
    <div class="text-center mb-2">
        <h2 style="margin:0;font-size:16px;">SBC. RESTO</h2>
        <div style="font-size:10px;">Jln. Pangsalatan No. 1<br>Telp: 0812-3456-7890</div>
    </div>
    
    <div class="border-top border-bottom mb-2 mt-2">
        <table style="margin:0;">
            <tr><td>No</td><td>: {{ $pesanan->no_pesanan }}</td></tr>
            <tr><td>Tgl</td><td>: {{ $pesanan->created_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Kasir</td><td>: {{ $pesanan->user->name ?? 'Kasir' }}</td></tr>
            <tr><td>Pelanggan</td><td>: {{ $pesanan->nama_pelanggan ?? 'Walk-in' }}</td></tr>
            @if($pesanan->no_meja)
            <tr><td>Meja</td><td>: {{ $pesanan->no_meja }}</td></tr>
            @endif
        </table>
    </div>

    <table>
        @foreach($pesanan->details as $detail)
        <tr>
            <td colspan="3" class="item-name">{{ $detail->menu->nama }}</td>
        </tr>
        <tr>
            <td style="width: 20%;">{{ $detail->jumlah }}x</td>
            <td style="width: 30%;">{{ number_format($detail->harga_satuan, 0, ',', '.') }}</td>
            <td class="text-right" style="width: 50%;">{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($detail->catatan)
        <tr>
            <td colspan="3" style="font-size: 10px; font-style: italic;">Catatan: {{ $detail->catatan }}</td>
        </tr>
        @endif
        @endforeach
    </table>

    <div class="border-top mt-2">
        <table style="margin:0;">
            <tr>
                <td class="font-bold">TOTAL</td>
                <td class="text-right font-bold">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
            </tr>
            @php
                $bayar = $pesanan->pembayaran->first();
            @endphp
            @if($bayar)
            <tr>
                <td>BAYAR ({{ strtoupper(optional($bayar->metode_pembayaran)->nama_metode ?? 'CASH') }})</td>
                <td class="text-right">Rp {{ number_format($bayar->jumlah_bayar, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>KEMBALI</td>
                <td class="text-right">Rp {{ number_format(max(0, $bayar->jumlah_bayar - $pesanan->total_tagihan), 0, ',', '.') }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="text-center mt-2 border-top" style="padding-top:10px; font-size:10px;">
        Terima kasih atas kunjungan Anda.<br>
        Silakan berkunjung kembali!
    </div>
    </div>
</body>
</html>