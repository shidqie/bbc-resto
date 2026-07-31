<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker Dapur - {{ $pesanan->nomor_pesanan }}</title>
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

    <div class="text-center" style="font-weight: bold; font-size: 13px; margin-bottom: 8px;">
        ** CHECKER DAPUR **
    </div>
    
    <div style="font-size: 13px; font-weight: bold; margin-bottom: 2px;">
        Queue No: {{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}
    </div>
    <div style="margin-bottom: 8px;">
        Table : {{ $pesanan->meja->nomor_meja ?? '-' }}
    </div>

    <table style="margin-bottom: 8px;">
        <tr>
            <td style="width: 50%; color: #555;">Date & Time</td>
            <td style="width: 50%; color: #555;">Staff</td>
        </tr>
        <tr>
            <td class="font-bold">{{ \Carbon\Carbon::parse($pesanan->dibuat_pada ?? now())->format('d/m/y, h:i A') }}</td>
            <td class="font-bold">{{ auth()->user()->nama ?? 'Kasir' }}</td>
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
        @foreach($pesanan->detail_pesanan as $item)
        <tr>
            <td class="item-name font-bold">
                {{ $item->menu->nama_menu ?? $item->nama_menu ?? 'Menu' }}
                @if($item->catatan)
                    <div class="notes">* {{ $item->catatan }}</div>
                @endif
            </td>
            <td class="text-right font-bold" style="width: 40px;">
                {{ $item->kuantitas }}
            </td>
        </tr>
        @endforeach
    </table>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
