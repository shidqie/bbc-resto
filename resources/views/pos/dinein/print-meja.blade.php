<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker Meja - {{ $pesanan->kode_pesanan }}</title>
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
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .header-title { font-size: 13px; font-weight: bold; margin-bottom: 6px; text-align: center; }
        .divider { border-bottom: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 1px 0; vertical-align: top; font-size: 11px; }
        .item-name { word-break: break-word; }
        .notes { font-size: 10px; font-style: italic; padding-left: 8px; }
        @media print {
            body { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="header-title">
        ** CHECKER MEJA **
    </div>
    
    <table>
        <tr>
            <td style="width: 70px;">No :</td>
            <td class="text-right">{{ $pesanan->id }}</td>
        </tr>
        <tr>
            <td>Invoice :</td>
            <td class="text-right">{{ $pesanan->kode_pesanan ?? ('DIN-'.str_pad($pesanan->id, 5, '0', STR_PAD_LEFT)) }}</td>
        </tr>
        <tr>
            <td>Tanggal :</td>
            <td class="text-right">{{ \Carbon\Carbon::parse($pesanan->created_at ?? now())->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td>Kasir :</td>
            <td class="text-right">{{ auth()->user()->name ?? 'Kasir' }}</td>
        </tr>
        <tr>
            <td>Meja :</td>
            <td class="text-right font-bold">{{ $pesanan->meja->nomor_meja ?? '-' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        <tr class="font-bold">
            <td>Item Name</td>
            <td class="text-right" style="width: 40px;">Qty</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table>
        @foreach($pesanan->items as $item)
        <tr>
            <td class="item-name font-bold">
                {{ $item->menu->nama ?? $item->nama_menu ?? 'Menu' }}
                @if($item->catatan)
                    <div class="notes">* {{ $item->catatan }}</div>
                @endif
            </td>
            <td class="text-right font-bold" style="width: 40px;">
                {{ $item->qty }}
            </td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <script>
        window.onload = function() {
            window.focus();
            window.print();
            setTimeout(function() {
                window.close();
            }, 500);
        };
    </script>
</body>
</html>
