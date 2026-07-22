<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Pembayaran - {{ $pesanan->kode_pesanan }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { 
            font-family: 'Courier New', Courier, monospace, monospace; 
            width: 280px; 
            margin: 0 auto; 
            padding: 10px 5px; 
            font-size: 13px; 
            color: #000;
            line-height: 1.3;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 2px; }
        .divider { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 1px dashed #000; padding-bottom: 4px; font-size: 12px; }
        td { padding: 3px 0; vertical-align: top; font-size: 12px; }
        @media print {
            body { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="text-center title">
        BBC RESTO
    </div>
    <div class="text-center" style="font-size: 11px;">
        Restoran & Kuliner Tradisional<br>
        Jl. BBC Resto No. 88, West Java<br>
        Telp: (022) 8829-1029
    </div>

    <div class="divider"></div>
    
    <div style="font-size: 12px;">
        <table>
            <tr>
                <td style="width: 70px;">No</td>
                <td>: {{ $pesanan->kode_pesanan ?? 'DIN-'.str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($pesanan->dibayar_pada ?? now())->format('d/m/Y H:i:s') }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>: {{ auth()->user()->name ?? 'Kasir Resto' }}</td>
            </tr>
            <tr>
                <td>Meja</td>
                <td class="font-bold">: {{ $pesanan->meja->nomor_meja ?? '-' }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td class="font-bold">: {{ $pesanan->nama_konsumen ?? '-' }}</td>
            </tr>
        </table>
    </div>
    
    <div class="divider"></div>
    
    <table>
        <thead>
            <tr>
                <th style="text-align: left;">Item Name</th>
                <th style="text-align: center; width: 35px;">Qty</th>
                <th style="text-align: right; width: 65px;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($pesanan->items as $item)
            @php 
                $hargaMenu = $item->menu->harga ?? 0;
                $subtotal = $item->qty * $hargaMenu; 
                $total += $subtotal;
            @endphp
            <tr>
                <td class="font-bold" style="padding-top: 4px;">{{ $item->menu->nama ?? $item->menu->nama_menu }}</td>
                <td class="text-center" style="padding-top: 4px;">{{ $item->qty }}</td>
                <td class="text-right font-bold" style="padding-top: 4px;">{{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="divider"></div>
    
    <table style="font-weight: bold; font-size: 13px;">
        <tr>
            <td>TOTAL TAGIHAN</td>
            <td class="text-right">Rp {{ number_format($pesanan->pembayaran->total ?? $total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>METODE BAYAR</td>
            <td class="text-right uppercase">{{ $pesanan->pembayaran->metode_bayar ?? 'TUNAI' }}</td>
        </tr>
        <tr>
            <td>STATUS</td>
            <td class="text-right uppercase" style="color: green;">LUNAS</td>
        </tr>
    </table>
    
    <div class="divider"></div>

    <div class="text-center font-bold" style="font-size: 11px; margin-top: 10px;">
        Terima Kasih Atas Kunjungan Anda!<br>
        ~ Selamat Menikmati ~
    </div>
    
    <div class="no-print" style="margin-top: 15px; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 12px; font-size: 12px; cursor: pointer;">Print Struk</button>
    </div>

</body>
</html>
