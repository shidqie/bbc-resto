<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker - {{ $pesanan->id_pesanan }}</title>
    <style>
        @page { size: 80mm auto; margin: 0; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            color: #000;
        }
        .preview-toolbar {
            background: #1f2937;
            color: white;
            padding: 12px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-family: sans-serif;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .preview-toolbar button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .preview-toolbar button.btn-cancel {
            background: #ef4444;
        }
        .receipt-wrapper {
            display: flex;
            justify-content: center;
            padding: 24px;
        }
        .receipt-content {
            background: white;
            width: 270px;
            padding: 12px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            font-size: 11px;
            line-height: 1.25;
            letter-spacing: -0.2px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .divider { border-bottom: 1px dashed #000; margin: 6px 0; }
        .section-spacer { margin-top: 24px; padding-top: 12px; }
        table { width: 100%; max-width: 270px; border-collapse: collapse; table-layout: fixed; }
        td { padding: 1px 0; vertical-align: top; font-size: 11px; }
        .item-name { word-break: break-word; }
        
        @media print {
            body { background: white; width: 270px !important; max-width: 270px !important; margin: 0 auto !important; padding: 0 !important; }
            .preview-toolbar { display: none !important; }
            .receipt-wrapper { padding: 0 !important; display: block !important; margin: 0 auto !important; }
            .receipt-content { width: 270px !important; max-width: 270px !important; padding: 0 !important; box-shadow: none !important; margin: 0 auto !important; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="preview-toolbar no-print">
        <div>
            <strong>Pratinjau Cetak</strong> - Checker
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-cancel" onclick="window.close()">Tutup</button>
            <button onclick="window.print()">🖨️ Cetak</button>
        </div>
    </div>

    <div class="receipt-wrapper">
        <div class="receipt-content">

    {{-- ═════════════════ BAGIAN 1: CHECKER MEJA ═════════════════ --}}
    <div class="text-center" style="margin-bottom: 8px;">
        ** CHECKER MEJA **
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


    {{-- ═════════════════ BAGIAN 2: CHECKER DAPUR ═════════════════ --}}
    <div class="section-spacer"></div>

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

        </div>
    </div>

    @if(request('auto_print') == '1')
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
    @endif
</body>
</html>