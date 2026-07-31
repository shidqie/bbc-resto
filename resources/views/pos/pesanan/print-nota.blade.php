<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $pesanan->nomor_pesanan }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body { 
            font-family: 'Courier New', Courier, monospace, monospace; 
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
        .font-bold { font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .subtitle { font-size: 10px; line-height: 1.2; margin-bottom: 4px; }
        .divider { border-bottom: 1px dashed #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; font-size: 11px; }
        .item-name { font-weight: bold; word-break: break-word; }
        .item-detail { font-size: 10px; padding-left: 6px; }
        .notes { font-size: 10px; font-style: italic; padding-left: 6px; }
        .qr-placeholder { margin: 8px auto 4px auto; text-align: center; }
        .legal-text { font-size: 9px; text-align: center; margin-top: 6px; line-height: 1.3; }
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
        Restoran & Kuliner Tradisional<br>
        Jl. Babakan Cinta No. 88, West Java<br>
        Telp: (022) 8829-1029 · NPWP: 01.234.567.8-901.000
    </div>

    <div class="divider"></div>
    
    <div class="text-center" style="font-weight: bold; font-size: 13px; margin-bottom: 8px;">
        Dine-In
    </div>
    
    <div style="font-size: 13px; font-weight: bold; margin-bottom: 2px;">
        Queue No: {{ str_pad($pesanan->id, 3, '0', STR_PAD_LEFT) }}
    </div>
    <div style="margin-bottom: 2px;">
        {{ $pesanan->nomor_pesanan ?? 'INV-'.str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}
    </div>
    <div style="margin-bottom: 8px;">
        Table : {{ $pesanan->meja->nomor_meja ?? '-' }}
    </div>

    <table style="margin-bottom: 8px;">
        <tr>
            <td style="width: 40%; color: #555;">Date & Time</td>
            <td style="width: 30%; color: #555;">Staff</td>
            <td style="width: 30%; color: #555;">Customer</td>
        </tr>
        <tr>
            <td class="font-bold">{{ \Carbon\Carbon::parse($pesanan->pembayaran->tanggal_pembayaran ?? $pesanan->diperbarui_pada ?? $pesanan->dibuat_pada ?? now())->format('d/m/y, h:i A') }}</td>
            <td class="font-bold">{{ auth()->user()->nama ?? 'Kasir' }}</td>
            <td class="font-bold">{{ $pesanan->nama_konsumen ?? 'Pelanggan' }}</td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    {{-- LIST ITEM MENURUT STANDAR INDONESIA (LINE 1: MENU, LINE 2: QTY x HARGA & TOTAL) --}}
    <table>
        @php 
            $subtotalTotal = 0; 
        @endphp
        @foreach($pesanan->detail_pesanan as $item)
        @php 
            $hargaSatuan = $item->menu->harga_jual ?? $item->harga_satuan ?? 0;
            $sub = $item->kuantitas * $hargaSatuan;
            $subtotalTotal += $sub;
        @endphp
        <tr>
            <td colspan="2" class="item-name">
                {{ $item->menu->nama_menu ?? $item->menu->nama_menu }}
            </td>
        </tr>
        <tr>
            <td class="item-detail">
                {{ $item->kuantitas }} x {{ number_format($hargaSatuan, 0, ',', '.') }}
                @if($item->catatan)
                    <div class="notes">* Catatan: {{ $item->catatan }}</div>
                @endif
            </td>
            <td class="text-right font-bold">{{ number_format($sub, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>
    
    <div class="divider"></div>
    
    {{-- RINGKASAN PEMBAYARAN RESMI --}}
    @php
        $pembayaran = $pesanan->pembayaran;
        $totalTagihan = $pembayaran->total ?? $subtotalTotal;
        $uangDiterima = $pembayaran->uang_diterima ?? $totalTagihan;
        $kembalian = max(0, $uangDiterima - $totalTagihan);
        $metodeBayar = strtoupper($pembayaran->metode_bayar ?? 'TUNAI');
    @endphp

    <table>
        <tr>
            <td>Sub Total</td>
            <td class="text-right font-bold">Rp {{ number_format($subtotalTotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Charge</td>
            <td class="text-right">Rp 0</td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="text-right">Rp 0</td>
        </tr>
        <tr class="font-bold">
            <td style="padding-top: 6px; font-size: 13px;">Total Amount</td>
            <td class="text-right" style="padding-top: 6px; font-size: 13px;">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- DETAIL CASH RECEIVED & KEMBALIAN --}}
    <table>
        <tr>
            <td>Metode Bayar</td>
            <td class="text-right font-bold">{{ $metodeBayar }}</td>
        </tr>
        <tr>
            <td>Uang Diterima</td>
            <td class="text-right font-bold">Rp {{ number_format($uangDiterima, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td class="text-right font-bold">Rp {{ number_format($kembalian, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td class="text-right font-bold">[ LUNAS ]</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- QR CODE DIGITAL RECEIPT --}}
    <div class="qr-placeholder">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode(route('pos.pesanan.print-nota', $pesanan->id)) }}" 
             alt="QR Struk Digital" style="width: 75px; height: 75px; margin: 0 auto; display: block;" />
        <div style="font-size: 9px; margin-top: 3px;">Scan untuk Struk Digital & Review</div>
    </div>

    <div class="legal-text">
        *** TERIMA KASIH ATAS KUNJUNGAN ANDA ***<br>
        Barang yang sudah dibeli tidak dapat ditukar / dikembalikan.
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
