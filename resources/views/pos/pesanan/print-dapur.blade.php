<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker Dapur - {{ $pesanan->id_pesanan }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 270px; 
            margin: 0 auto; 
            padding: 8px 4px; 
            font-size: 11px; 
            color: #000;
            line-height: 1.25;
            letter-spacing: -0.2px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .divider { border-bottom: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; font-size: 11px; }
        .item-name { word-break: break-word; }
        @media print {
            body { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="text-center" style="margin-bottom: 8px;">
        ** CHECKER DAPUR **
    </div>
    
    <table style="margin-bottom: 8px;">
        <tr>
            <td style="width: 25%;">No</td>
            <td style="width: 5%;">:</td>
            <td style="width: 70%; text-align: right;">{{ $pesanan->id }}</td>
        </tr>
        <tr>
            <td>Invoice</td>
            <td>:</td>
            <td style="text-align: right;">{{ $pesanan->id_pesanan ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>:</td>
            <td style="text-align: right;">{{ \Carbon\Carbon::parse($pesanan->dibuat_pada ?? now())->format('j/n/Y G:i:s') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td style="text-align: right;">{{ auth()->user()->nama ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Meja</td>
            <td>:</td>
            <td style="text-align: right;">{{ preg_replace('/[^0-9]/', '', $pesanan->meja->nomor_meja ?? '-') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- TABLE HEADER ITEM NAME & QTY --}}
    <table>
        <tr>
            <td>Item Name</td>
            <td class="text-right" style="width: 40px;">Qty</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- ITEM LIST --}}
    <table>
        @foreach($pesanan->detail_pesanan as $item)
        <tr>
            <td class="item-name">
                {{ $item->menu->nama_menu ?? $item->menu->nama ?? 'Menu' }}
                @if($item->catatan)
                    <br><span style="font-style: italic;">* {{ $item->catatan }}</span>
                @endif
            </td>
            <td class="text-right" style="width: 40px;">
                {{ $item->jumlah }}
            </td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>