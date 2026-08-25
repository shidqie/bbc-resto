@php
    \Carbon\Carbon::setLocale('id');

    $namaPemesan = $pesanan->nama_konsumen;
    if (!$namaPemesan && $pesanan->catatan) {
        if (preg_match('/Self-Order QR \((.*?)\)/', $pesanan->catatan, $m)) {
            $namaPemesan = $m[1];
        } elseif (preg_match('/Pemesan:\s*([^|]+)/', $pesanan->catatan, $m)) {
            $namaPemesan = trim($m[1]);
        }
    }
    if (!$namaPemesan && $pesanan->pelanggan) {
        $namaPemesan = $pesanan->pelanggan->nama;
    }
    if (!$namaPemesan) {
        $namaPemesan = 'Tamu';
    }

    $subtotal = (float)($pesanan->jumlah_sebelum_potongan ?: $pesanan->detail_pesanan->sum('subtotal'));
    $biayaLayanan = (float)($pesanan->biaya_pelayanan ?? 0);
    $totalPesanan = (float)($pesanan->total_tagihan ?: ($subtotal + $biayaLayanan));
    $isLunas = in_array(optional($pesanan->status_pembayaran)->id ?? 0, [2, 3]) || in_array($pesanan->status, ['lunas', 'selesai']);

    $mejaStr = '-';
    if ($pesanan->meja) {
        $mejaStr = Str::startsWith($pesanan->meja->nomor_meja, 'Meja') ? $pesanan->meja->nomor_meja : 'Meja ' . $pesanan->meja->nomor_meja;
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pesanan - {{ $pesanan->id_pesanan }}</title>
    <style>
        @page {
            margin: 18pt 14pt 18pt 14pt;
        }
        * {
            box-sizing: border-box;
            font-family: 'Courier', 'Courier New', monospace !important;
        }
        body {
            font-family: 'Courier', 'Courier New', monospace !important;
            color: #000000;
            font-size: 8pt;
            line-height: 1.35;
            background: #ffffff;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .header {
            text-align: center;
            margin-bottom: 6px;
        }
        .header .title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
            color: #000000;
        }
        .header .resto {
            font-size: 8.5pt;
            color: #000000;
        }
        
        .divider {
            border-bottom: 1px dashed #000000;
            margin: 5px 0;
        }
        
        table.w-full {
            width: 100%;
            border-collapse: collapse;
        }
        
        table.info-table td {
            padding: 1.5px 0;
            font-size: 8pt;
            vertical-align: top;
        }
        .label {
            color: #000000;
            width: 44%;
        }
        .val {
            text-align: right;
            font-weight: bold;
            color: #000000;
            width: 56%;
        }
        
        .section-title {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-top: 3px;
            margin-bottom: 2px;
            color: #000000;
        }
        
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2px;
        }
        table.items-table th {
            font-size: 8pt;
            text-transform: uppercase;
            border-bottom: 1px dashed #000000;
            padding: 2.5px 0;
            font-weight: bold;
            color: #000000;
        }
        table.items-table td {
            padding: 2.5px 0;
            font-size: 8pt;
            vertical-align: top;
        }
        .note {
            font-size: 7pt;
            color: #444444;
            font-style: italic;
            margin-top: 1px;
        }
        
        .calc-table td {
            padding: 1.5px 0;
            font-size: 8pt;
        }
        .calc-table .total td {
            font-size: 8pt;
            font-weight: bold;
            padding-top: 3px;
            border-top: 1px dashed #000000;
            color: #000000;
        }
        
        .status-badge-unpaid {
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
        }
        .status-badge-paid {
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
        }
        
        .footer-note {
            text-align: center;
            font-size: 7pt;
            line-height: 1.3;
            color: #222222;
            font-style: italic;
            margin-top: 6px;
            padding: 0 2px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <div class="title">BUKTI PESANAN DINE-IN</div>
        <div class="resto">Rumah Makan Saung Babakan Cinta</div>
    </div>

    <div class="divider"></div>

    {{-- Info Pesanan --}}
    <table class="w-full info-table">
        <tr>
            <td class="label">Kode Pesanan:</td>
            <td class="val">{{ $pesanan->id_pesanan }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal:</td>
            <td class="val">{{ \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->locale('id')->isoFormat('D MMM Y, HH.mm') }} WIB</td>
        </tr>
        <tr>
            <td class="label">Meja:</td>
            <td class="val">{{ $mejaStr }}</td>
        </tr>
        <tr>
            <td class="label">Nama Pemesan:</td>
            <td class="val">{{ $namaPemesan }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Rincian Pesanan --}}
    <div class="section-title">Rincian Pesanan</div>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-left" style="width: 46%;">Menu</th>
                <th class="text-center" style="width: 10%;">Qty</th>
                <th class="text-right" style="width: 22%;">Harga</th>
                <th class="text-right" style="width: 22%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pesanan->detail_pesanan as $item)
            <tr>
                <td class="text-left">
                    <div class="font-bold">
                        {{ optional($item->menu)->nama_menu ?? optional($item->menu)->nama ?? 'Menu' }}
                        @if($item->is_tambahan)
                            <span style="font-size: 7pt; font-weight: normal;">[Tambahan]</span>
                        @endif
                    </div>
                    @if($item->catatan)
                        <div class="note">({{ $item->catatan }})</div>
                    @endif
                </td>
                <td class="text-center font-bold">{{ $item->jumlah }}</td>
                <td class="text-right">Rp{{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right font-bold">Rp{{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    {{-- Perhitungan Total --}}
    <table class="w-full calc-table">
        <tr>
            <td class="text-left">Subtotal:</td>
            <td class="text-right font-bold">Rp{{ number_format($subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($biayaLayanan > 0)
        <tr>
            <td class="text-left">Biaya Layanan:</td>
            <td class="text-right font-bold">Rp{{ number_format($biayaLayanan, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total">
            <td class="text-left font-bold">Total Pesanan:</td>
            <td class="text-right font-bold">Rp{{ number_format($totalPesanan, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Status Pembayaran --}}
    <table class="w-full info-table">
        <tr>
            <td class="label">Metode Pembayaran:</td>
            <td class="val">Bayar di Kasir</td>
        </tr>
        <tr>
            <td class="label">Status Pembayaran:</td>
            <td class="val">
                @if($isLunas)
                    <span class="status-badge-paid">LUNAS</span>
                @else
                    <span class="status-badge-unpaid">BELUM DIBAYAR</span>
                @endif
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Catatan Kaki --}}
    <div class="footer-note">
        Bukti ini menunjukkan bahwa pesanan telah tercatat pada sistem. Pembayaran dilakukan di kasir.
    </div>

</body>
</html>
