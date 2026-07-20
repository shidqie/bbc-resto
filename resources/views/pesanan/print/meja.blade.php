<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Meja #{{ $pesanan->no_pesanan }}</title>
    <style>
        body { font-family: sans-serif; color: #000; max-width: 300px; margin: 0 auto; padding: 20px; text-align: center; }
        .meja-box { border: 4px solid #000; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .meja-label { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
        .meja-number { font-size: 80px; font-weight: bold; line-height: 1; }
        .order-info { font-size: 16px; margin-bottom: 5px; }
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
    <div class="meja-box">
        <div class="meja-label">NOMOR MEJA</div>
        <div class="meja-number">{{ $pesanan->no_meja ?? '-' }}</div>
    </div>
    
    <div class="order-info">
        <strong>No. Pesanan:</strong><br>
        {{ $pesanan->no_pesanan }}
    </div>
    
    <div class="order-info" style="margin-top: 15px;">
        <strong>Jenis:</strong><br>
        {{ strtoupper(str_replace('_', ' ', $pesanan->jenis_pesanan)) }}
    </div>
    
    <div class="order-info" style="margin-top: 15px; font-size: 12px;">
        {{ $pesanan->created_at->format('d M Y H:i') }}
    </div>
    </div>
</body>
</html>