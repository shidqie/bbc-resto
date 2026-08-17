<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $pesanan->id_pesanan }}</title>
    <style>
        @page { size: {{ request('paper_size', '58') }}mm auto; margin: 0; }
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
            width: {{ request('paper_size', '58') == '80' ? '270px' : '200px' }};
            padding: 12px;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
            font-size: 11px;
            line-height: 1.25;
            letter-spacing: -0.2px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        table { width: 100%; max-width: 270px; border-collapse: collapse; table-layout: fixed; }
        td { padding: 1px 0; vertical-align: top; font-size: 11px; }
        .item-name { word-break: break-word; }
        
        @media print {
            body { background: white; }
            .preview-toolbar { display: none !important; }
            .receipt-wrapper { padding: 0; display: block; }
            .receipt-content { width: 72mm; padding: 0; box-shadow: none; margin: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="preview-toolbar no-print">
        <div>
            <strong>Pratinjau Cetak</strong> - Struk
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="btn-cancel" onclick="window.close()">Tutup</button>
            <button onclick="window.print()">🖨️ Cetak</button>
        </div>
    </div>

    <div class="receipt-wrapper">
        <div class="receipt-content">

    <div class="text-center">
        Saung babakan cinta<br>
        @if(request('show_alamat', '1') == '1')
        Jl. Ciloa No.km 6,<br>
        Pasirhalang, Kec. Cisarua,<br>
        Kabupaten Bandung Barat, Jawa<br>
        Barat 40551, Indonesia,<br>
        40551<br>
        @endif
        @if(request('show_telepon', '1') == '1')
        Phone: 081394616635
        @endif
    </div>

    <div class="text-center" style="margin: 6px 0;">================================</div>
    
    <table>
        @if(request('show_waktu', '1') == '1')
        <tr>
            <td style="width: 38%;">Date</td>
            <td style="width: 5%;">:</td>
            <td style="width: 57%;">{{ \Carbon\Carbon::parse($pesanan->pembayaran->first()->dibayar_pada ?? $pesanan->diperbarui_pada ?? $pesanan->dibuat_pada ?? now())->format('d/m/Y H:i') }}</td>
        </tr>
        @endif
        <tr>
            <td>Order Number</td>
            <td>:</td>
            <td>{{ $pesanan->id_pesanan }}</td>
        </tr>
        @if(request('show_pelanggan', '1') == '1')
        <tr>
            <td>Customer</td>
            <td>:</td>
            <td>{{ $pesanan->catatan ?? 'Pelanggan' }}</td>
        </tr>
        @endif
        @if(request('show_meja', '1') == '1' && $pesanan->meja)
        <tr>
            <td>Meja</td>
            <td>:</td>
            <td>{{ $pesanan->meja->nomor_meja }}</td>
        </tr>
        @endif
        @if(request('show_kasir', '1') == '1')
        <tr>
            <td>Cashier</td>
            <td>:</td>
            <td>{{ auth()->user()->nama ?? 'Kasir' }}</td>
        </tr>
        @endif
    </table>
    
    <div class="text-center" style="margin: 6px 0;">================================</div>
    <div class="text-center">** REPRINT BILL **</div>
    <div class="text-center" style="margin: 6px 0;">================================</div>
    
    <table>
        @php 
            $subtotalTotal = 0; 
            $totalItem = 0;
        @endphp
        
        @if(isset($pesanan->jumlah_pajak) && $pesanan->jumlah_pajak > 0)
        <tr>
            <td class="item-name">Layanan</td>
            <td class="text-right">{{ number_format($pesanan->jumlah_pajak, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">1x {{ number_format($pesanan->jumlah_pajak, 0, ',', '.') }}</td>
        </tr>
        @endif

        @foreach($pesanan->detail_pesanan as $item)
        @php 
            $hargaSatuan = $item->harga_satuan ?? $item->menu->harga_jual ?? 0;
            $sub = $item->jumlah * $hargaSatuan;
            $subtotalTotal += $sub;
            $totalItem += $item->jumlah;
        @endphp
        <tr>
            <td class="item-name">{{ $item->menu->nama_menu ?? $item->menu->nama ?? 'Menu' }}</td>
            <td class="text-right">{{ number_format($sub, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">{{ $item->jumlah }}x {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>
    
    <div class="text-center" style="margin: 6px 0;">--------------------------------</div>
    
    <div>Total Item {{ $totalItem }}</div>
    <br>
    
    @php
        $pembayaran = $pesanan->pembayaran->first();
        $totalTagihan = $pesanan->total_tagihan;
        $uangDiterima = $pembayaran->jumlah_bayar ?? $totalTagihan;
        $kembalian = max(0, $uangDiterima - $totalTagihan);
        $metodeBayar = ucfirst(strtolower($pembayaran->metode_pembayaran ?? 'Cash'));
    @endphp

    <table>
        <tr>
            <td>Total</td>
            <td class="text-right">{{ number_format($totalTagihan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">Tender</td>
        </tr>
        <tr>
            <td>{{ $metodeBayar }}</td>
            <td class="text-right">{{ number_format($uangDiterima, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Change</td>
            <td class="text-right">{{ number_format($kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>

    <br>
    
    @if(request('show_footer', '1') == '1')
    <div class="text-center">
        Thank you for your visit!
    </div>
    @endif
    
    @if(request('show_pencetak', '1') == '1')
    <div class="text-center" style="font-size: 9px; margin-top: 4px;">
        Printed by {{ auth()->user()->nama ?? 'System' }} ({{ now()->format('d/m/Y, H:i') }})
    </div>
    @endif

    @if(request('show_branding', '1') == '1')
    <div class="text-center" style="font-size: 9px; margin-top: 4px;">
        Powered by BBC-Resto
    </div>
    @endif

        </div>
    </div>

    @if(request('preview') != '1' && request('auto_print', '1') == '1')
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