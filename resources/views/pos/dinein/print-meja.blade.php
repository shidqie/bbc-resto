<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checker Meja - {{ $pesanan->kode_pesanan }}</title>
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
        .title { font-size: 15px; font-weight: bold; margin-bottom: 2px; }
        .subtitle { font-size: 11px; margin-bottom: 6px; }
        .divider { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        th { border-bottom: 1px dashed #000; padding-bottom: 4px; text-align: left; font-size: 12px; }
        td { padding: 3px 0; vertical-align: top; font-size: 12px; }
        .notes { font-size: 11px; font-style: italic; padding-left: 8px; }
        @media print {
            body { width: 100%; padding: 0; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="text-center title">
        SAUNG BABAKAN CINTA
    </div>
    <div class="text-center subtitle">
        ** CHECKER MEJA **
    </div>
    
    <div style="margin-top: 6px;">
        <table style="font-size: 12px;">
            <tr>
                <td style="width: 75px;">No Pesanan</td>
                <td>: {{ $pesanan->kode_pesanan ?? 'DIN-'.str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ \Carbon\Carbon::parse($pesanan->dibuka_pada)->format('d/m/Y H:i:s') }}</td>
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
                <th style="text-align: left;">Menu</th>
                <th style="text-align: center; width: 35px;">Qty</th>
                <th style="text-align: right; width: 70px;">Harga</th>
            </tr>
        </thead>
        <tbody>
            @php $subtotalTotal = 0; @endphp
            @foreach($pesanan->items as $item)
            @php 
                $sub = $item->subtotal ?? ($item->qty * $item->harga_satuan);
                $subtotalTotal += $sub;
            @endphp
            <tr>
                <td class="font-bold">
                    {{ $item->menu->nama ?? $item->menu->nama_menu }}
                    @if($item->catatan)
                        <div class="notes">* Catatan: {{ $item->catatan }}</div>
                    @endif
                </td>
                <td class="text-center font-bold" style="font-size: 13px;">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($sub, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table style="font-size: 12px;">
        <tr>
            <td class="font-bold">TOTAL PENJUALAN</td>
            <td class="text-right font-bold" style="font-size: 14px;">Rp {{ number_format($subtotalTotal, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="text-center font-bold" style="font-size: 11px; margin-top: 6px;">
        === MOHON DISAJIKAN DI MEJA ===
    </div>

    <div class="no-print" style="margin-top: 15px; text-align: center;">
        <button onclick="window.print()" style="padding: 6px 14px; font-size: 12px; font-weight: bold; cursor: pointer;">Cetak Struk Meja</button>
    </div>

</body>
</html>
