<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran - {{ $pesanan->kode_pesanan }}</title>
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
    
    <table>
        <tr>
            <td style="width: 75px;">No. Struk</td>
            <td>: {{ $pesanan->kode_pesanan ?? 'DIN-'.str_pad($pesanan->id, 5, '0', STR_PAD_LEFT) }}</td>
        </tr>
        <tr>
            <td>Tanggal</td>
            <td>: {{ \Carbon\Carbon::parse($pesanan->dibayar_pada ?? $pesanan->created_at ?? now())->format('d/m/Y H:i:s') }}</td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>: {{ auth()->user()->name ?? 'Kasir Resto' }}</td>
        </tr>
        <tr>
            <td>Meja / Tipe</td>
            <td class="font-bold">: Meja {{ $pesanan->meja->nomor_meja ?? '-' }} (Dine In)</td>
        </tr>
        <tr>
            <td>Pelanggan</td>
            <td class="font-bold">: {{ $pesanan->nama_konsumen ?? 'Pelanggan' }}</td>
        </tr>
    </table>
    
    <div class="divider"></div>
    
    {{-- LIST ITEM MENURUT STANDAR INDONESIA (LINE 1: MENU, LINE 2: QTY x HARGA & TOTAL) --}}
    <table>
        @php 
            $subtotalTotal = 0; 
        @endphp
        @foreach($pesanan->items as $item)
        @php 
            $hargaSatuan = $item->menu->harga ?? $item->harga_satuan ?? 0;
            $sub = $item->qty * $hargaSatuan;
            $subtotalTotal += $sub;
        @endphp
        <tr>
            <td colspan="2" class="item-name">
                {{ $item->menu->nama ?? $item->menu->nama_menu }}
            </td>
        </tr>
        <tr>
            <td class="item-detail">
                {{ $item->qty }} x {{ number_format($hargaSatuan, 0, ',', '.') }}
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
            <td>Subtotal</td>
            <td class="text-right font-bold">Rp {{ number_format($subtotalTotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>PB1 / Tax (0%)</td>
            <td class="text-right">Rp 0</td>
        </tr>
        <tr class="font-bold">
            <td style="padding-top: 4px;">TOTAL TAGIHAN</td>
            <td class="text-right" style="padding-top: 4px; font-size: 12px;">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
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
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=80x80&data={{ urlencode(route('pos.dinein.print-nota', $pesanan->id)) }}" 
             alt="QR Struk Digital" style="width: 75px; height: 75px; margin: 0 auto; display: block;" />
        <div style="font-size: 9px; margin-top: 3px;">Scan untuk Struk Digital & Review</div>
    </div>

    <div class="legal-text">
        *** TERIMA KASIH ATAS KUNJUNGAN ANDA ***<br>
        Barang yang sudah dibeli tidak dapat ditukar / dikembalikan.
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
