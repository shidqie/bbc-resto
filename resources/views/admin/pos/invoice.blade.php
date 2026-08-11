<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Receipt #{{ $kodePesanan }}</title>
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
        .text-left { text-align: left; }
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

    @php
        $pembayaranTerakhir = $pesanan->pembayaran ? $pesanan->pembayaran->last() : null;
        $metode = $pembayaranTerakhir ? strtoupper($pembayaranTerakhir->metode_pembayaran ?? 'CASH') : 'CASH';
        $jumlahBayar = $pembayaranTerakhir ? $pembayaranTerakhir->jumlah_bayar : $pesanan->total_harga;
        $kembalian = max(0, $jumlahBayar - $pesanan->total_harga);
    @endphp

    <div class="text-center">
        Saung babakan cinta<br>
        Jl. Ciloa No.km 6,<br>
        Pasirhalang, Kec. Cisarua,<br>
        Kabupaten Bandung Barat, Jawa<br>
        Barat 40551, Indonesia,<br>
        Phone: 081394616635
    </div>

    <div class="text-center" style="margin: 6px 0;">================================</div>
    
    <table>
        <tr>
            <td style="width: 38%;">Date</td>
            <td style="width: 5%;">:</td>
            <td style="width: 57%;">{{ $pesanan->tanggal_pesanan ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d/m/Y H:i') : date('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Order Number</td>
            <td>:</td>
            <td>{{ $kodePesanan }}</td>
        </tr>
        <tr>
            <td>Customer</td>
            <td>:</td>
            <td>{{ $pesanan->nama_pelanggan ?? 'Pelanggan Umum' }}</td>
        </tr>
        <tr>
            <td>Sales Type</td>
            <td>:</td>
            <td>Dine-In{{ $pesanan->no_meja ? ' (Meja ' . $pesanan->no_meja . ')' : '' }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td>:</td>
            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $pesanan->status_pesanan ?? '-') }}</td>
        </tr>
    </table>
    
    <div class="text-center" style="margin: 6px 0;">================================</div>
    <div class="text-center">** E-RECEIPT **</div>
    <div class="text-center" style="margin: 6px 0;">================================</div>
    
    <table>
        @php 
            $subtotalTotal = 0; 
            $totalItem = 0;
        @endphp

        @foreach($pesanan->details ?? [] as $item)
        @php 
            $hargaSatuan = $item->harga_satuan ?? 0;
            $sub = $item->jumlah * $hargaSatuan;
            $subtotalTotal += $sub;
            $totalItem += $item->jumlah;
        @endphp
        <tr>
            <td class="item-name">{{ $item->menu->nama ?? 'Menu' }}</td>
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
    
    <table>
        <tr>
            <td>Total Tagihan</td>
            <td class="text-right">{{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">Tender</td>
        </tr>
        <tr>
            <td>{{ $metode }}</td>
            <td class="text-right">{{ number_format($jumlahBayar, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Change</td>
            <td class="text-right">{{ number_format($kembalian, 0, ',', '.') }}</td>
        </tr>
    </table>

    <br>
    <div class="text-center">
        email: saungbbc996@gmail.com
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
