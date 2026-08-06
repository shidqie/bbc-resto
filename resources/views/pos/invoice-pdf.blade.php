<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Pesanan #{{ $pesanan->no_pesanan ?? $pesanan->id }}</title>
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
        $isCatering = $type === 'catering';
        $jenisStr = $isCatering ? 'Catering' : 'Nasi Box';
        $qty = $pesanan->jumlah_porsi ?? $pesanan->jumlah_box ?? 1;
        
        $dpPaid = isset($pesanan->dp_amount) && $pesanan->dp_amount > 0;
        $statusBayar = strtolower($pesanan->status_bayar ?? 'Belum Bayar');
        $isLunas = in_array($statusBayar, ['lunas', 'paid']);
        $sisaTagihan = max(0, $pesanan->total_tagihan - ($pesanan->dp_amount ?? 0));
        
        $ongkir = $pesanan->ongkos_kirim ?? 0;
        $subtotalPaket = max(0, $pesanan->total_tagihan - $ongkir);
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
            <td style="width: 57%;">{{ $pesanan->created_at ? \Carbon\Carbon::parse($pesanan->created_at)->format('d/m/Y H:i') : date('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td>Order Number</td>
            <td>:</td>
            <td>{{ $pesanan->no_pesanan ?? $pesanan->id }}</td>
        </tr>
        <tr>
            <td>Customer</td>
            <td>:</td>
            <td>{{ $pesanan->nama_pemesan ?? 'Pelanggan' }}</td>
        </tr>
        <tr>
            <td>Contact</td>
            <td>:</td>
            <td>{{ $pesanan->kontak ?? $pesanan->nomor_wa ?? $pesanan->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td>Event Date</td>
            <td>:</td>
            <td>{{ $pesanan->tanggal_acara ? \Carbon\Carbon::parse($pesanan->tanggal_acara)->format('d/m/Y') : '-' }}</td>
        </tr>
        @if(isset($pesanan->metode_pengiriman))
        <tr>
            <td>Delivery</td>
            <td>:</td>
            <td style="text-transform: capitalize;">{{ $pesanan->metode_pengiriman }}</td>
        </tr>
        @endif
        <tr>
            <td>Location</td>
            <td>:</td>
            <td>{{ $pesanan->lokasi_acara ?? $pesanan->alamat_lengkap ?? '-' }}</td>
        </tr>
        <tr>
            <td>Sales Type</td>
            <td>:</td>
            <td>{{ $jenisStr }}</td>
        </tr>
        <tr>
            <td>Order Status</td>
            <td>:</td>
            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $pesanan->status ?? '-') }}</td>
        </tr>
        <tr>
            <td>Pay Status</td>
            <td>:</td>
            <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $statusBayar) }}</td>
        </tr>
    </table>
    
    <div class="text-center" style="margin: 6px 0;">================================</div>
    <div class="text-center">** INVOICE {{ strtoupper($jenisStr) }} **</div>
    <div class="text-center" style="margin: 6px 0;">================================</div>
    
    <table>
        <tr>
            <td class="item-name">{{ $isCatering ? ($pesanan->paket->nama_paket ?? 'Paket Katering') : ($pesanan->paket->nama_paket ?? 'Menu Nasi Box') }}</td>
            <td class="text-right">{{ number_format($subtotalPaket, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">{{ $qty }}x {{ number_format($subtotalPaket / ($qty > 0 ? $qty : 1), 0, ',', '.') }}</td>
        </tr>
        
        @if(isset($pesanan->details) && count($pesanan->details) > 0)
            @foreach($pesanan->details as $d)
                <tr>
                    <td colspan="2" style="padding-left: 8px;">- {{ $d->menu->nama_menu ?? $d->menu->nama ?? 'Item' }}</td>
                </tr>
            @endforeach
        @endif
        
        @if($ongkir > 0)
        <tr>
            <td class="item-name">Ongkos Kirim</td>
            <td class="text-right">{{ number_format($ongkir, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>
    
    <div class="text-center" style="margin: 6px 0;">--------------------------------</div>
    
    <table>
        <tr>
            <td>Total Tagihan</td>
            <td class="text-right">{{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
        </tr>
        @if($dpPaid)
        <tr>
            <td>DP Terbayar</td>
            <td class="text-right">-{{ number_format($pesanan->dp_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Sisa Pembayaran</td>
            <td class="text-right">{{ number_format($sisaTagihan, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($sisaTagihan > 0 && !$isLunas)
        <tr>
            <td colspan="2" style="font-size: 10px; font-style: italic; text-align: center; margin-top: 4px;">
                Harap lunasi H-2 acara: {{ \Carbon\Carbon::parse($pesanan->tanggal_acara)->subDays(2)->format('d/m/Y') }}
            </td>
        </tr>
        @endif
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
