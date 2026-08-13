<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $pesanan->id_pesanan }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
            background: #fff;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .header-title {
            font-size: 32px;
            font-weight: 800;
            font-family: 'Times New Roman', Times, serif;
            color: #000;
            margin: 0;
        }
        .meta-table {
            font-size: 11px;
            text-align: right;
            line-height: 1.4;
        }
        .meta-table td {
            padding: 1px 4px;
        }
        .meta-label {
            color: #6B7280;
            font-weight: 600;
        }
        .meta-value {
            font-weight: 700;
            color: #111827;
        }
        .divider {
            border-bottom: 2px solid #111827;
            margin-bottom: 20px;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .info-grid td {
            width: 33.33%;
            vertical-align: top;
            padding-right: 15px;
        }
        .info-title {
            font-size: 11px;
            font-weight: 700;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .info-body {
            font-size: 11px;
            line-height: 1.5;
            color: #111827;
        }
        .info-name {
            font-size: 12px;
            font-weight: 700;
            color: #000;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background: #F3F4F6;
            color: #374151;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-top: 1px solid #E5E7EB;
            border-bottom: 1px solid #E5E7EB;
            text-align: left;
        }
        .items-table th.text-right, .items-table td.text-right {
            text-align: right;
        }
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #F3F4F6;
            font-size: 11px;
        }
        .items-table tr:nth-child(even) {
            background-color: #FAFAFA;
        }
        .summary-wrapper {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .summary-wrapper td {
            vertical-align: top;
        }
        .payment-notes {
            width: 55%;
            font-size: 11px;
        }
        .totals-table {
            width: 45%;
            border-collapse: collapse;
            float: right;
        }
        .totals-table td {
            padding: 4px 0;
            font-size: 11px;
        }
        .totals-table .label {
            color: #4B5563;
            font-weight: 600;
        }
        .totals-table .value {
            text-align: right;
            font-weight: 700;
            color: #111827;
        }
        .balance-due-row td {
            padding-top: 8px;
            border-top: 2px solid #111827;
            border-bottom: 3px double #111827;
            font-size: 13px;
            font-weight: 800;
        }
        .signature-table {
            width: 100%;
            margin-top: 50px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
        }
        .signature-line {
            width: 160px;
            border-bottom: 1px solid #6B7280;
            margin: 50px auto 6px auto;
        }
        .signature-title {
            font-size: 11px;
            font-weight: 700;
            color: #4B5563;
        }
        .signature-name {
            font-size: 11px;
            font-weight: 700;
            color: #111827;
        }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <h1 class="header-title">Invoice</h1>
            </td>
            <td style="vertical-align: top; text-align: right;">
                <table class="meta-table" style="margin-left: auto;">
                    <tr>
                        <td class="meta-label">No. Invoice:</td>
                        <td class="meta-value">{{ $pesanan->id_pesanan }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tanggal Invoice:</td>
                        <td class="meta-value">{{ \Carbon\Carbon::parse($pesanan->dibuat_pada ?? now())->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label">Tanggal Acara:</td>
                        <td class="meta-value">{{ $pesanan->jadwal_pesanan ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d/m/Y') : '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- 3-Column Info (From, Bill to, Ship to) --}}
    <table class="info-grid">
        <tr>
            <td>
                <div class="info-title">Dari</div>
                <div class="info-body">
                    <div class="info-name">Saung Babakan Cinta</div>
                    <div>Jl. Ciloa No.km 6, Pasirhalang</div>
                    <div>Kec. Cisarua, KBB 40551</div>
                    <div>WA: +62 813-9461-6635</div>
                    <div>saungbabakancinta@gmail.com</div>
                </div>
            </td>
            <td>
                <div class="info-title">Ditagihkan Kepada</div>
                <div class="info-body">
                    <div class="info-name">{{ $namaPemesan }}</div>
                    <div>{{ $kontak ?? '-' }}</div>
                    <div>{{ $pesanan->pelanggan->alamat ?? optional($pesanan->jadwal_pesanan)->alamat_pengiriman ?? 'Alamat Pemesan' }}</div>
                </div>
            </td>
            <td>
                <div class="info-title">Dikirim Kepada</div>
                <div class="info-body">
                    <div class="info-name">Layanan {{ $type === 'nasi_box' ? 'Nasi Box' : ($type === 'catering' ? 'Katering' : 'Resto') }}</div>
                    <div>{{ optional($pesanan->jadwal_pesanan)->alamat_pengiriman ?? '-' }}</div>
                    <div>Acara: {{ optional($pesanan->jadwal_pesanan)->tanggal_acara ? \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->format('d/m/Y H:i') : '-' }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 45%;">DESKRIPSI</th>
                <th class="text-right" style="width: 20%;">HARGA (IDR)</th>
                <th class="text-right" style="width: 15%;">JUMLAH</th>
                <th class="text-right" style="width: 20%;">TOTAL (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $subtotalCalc = 0; 
            @endphp
            @foreach($pesanan->detail_pesanan as $detail)
                @php
                    $hargaSatuan = $detail->harga_satuan ?? optional($detail->menu)->harga_jual ?? 0;
                    $amountLine = $detail->jumlah * $hargaSatuan;
                    $subtotalCalc += $amountLine;
                @endphp
                <tr>
                    <td>
                        <strong style="font-size: 11px;">{{ $detail->menu->nama_menu ?? 'Paket Menu' }}</strong>
                        @if($detail->pilihan_pesanan_catering->count() > 0)
                            <div style="font-size: 10px; color: #6B7280; margin-top: 2px;">
                                Opsi: 
                                {{ $detail->pilihan_pesanan_catering->map(fn($p) => $p->pilihan_komponen_paket->nama_pilihan ?? '')->implode(', ') }}
                            </div>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                    <td class="text-right">{{ $detail->jumlah }}</td>
                    <td class="text-right"><strong>Rp {{ number_format($amountLine, 0, ',', '.') }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Summary Breakdown --}}
    @php
        $ongkir = optional($pesanan->pengiriman)->biaya_pengiriman ?? 0;
        $totalTagihan = $pesanan->total_tagihan;
        $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $sisaTagihan = max(0, $totalTagihan - $dpTerbayar);
        $dpPersen = $pesanan->persentaseDP();
    @endphp

    <table class="summary-wrapper">
        <tr>
            <td class="payment-notes">
                <div class="info-title">Instruksi Pembayaran</div>
                <div style="font-size: 10px; color: #4B5563; line-height: 1.4;">
                    Transfer Bank BCA: <strong>2780378231</strong> a/n <strong>HENI</strong><br>
                    Status Pembayaran: <strong>{{ $dpTerbayar >= $totalTagihan ? 'LUNAS' : ($dpTerbayar > 0 ? 'DP TERBAYAR (' . $dpPersen . '%)' : 'MENUNGGU PEMBAYARAN') }}</strong>
                </div>
            </td>
            <td>
                <table class="totals-table">
                    <tr>
                        <td class="label">Subtotal:</td>
                        <td class="value">Rp {{ number_format($subtotalCalc, 0, ',', '.') }}</td>
                    </tr>
                    @if($ongkir > 0)
                    <tr>
                        <td class="label">Biaya Pengiriman:</td>
                        <td class="value">Rp {{ number_format($ongkir, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="label">Total Tagihan:</td>
                        <td class="value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Total Dibayar:</td>
                        <td class="value" style="color: #059669;">Rp {{ number_format($dpTerbayar, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="balance-due-row">
                        <td class="label" style="color: #000;">Sisa Tagihan:</td>
                        <td class="value" style="color: #000;">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Signature Section --}}
    <table class="signature-table">
        <tr>
            <td>
                <div class="signature-title">Tanda Tangan Pemesan</div>
                <div class="signature-line"></div>
                <div class="signature-name">{{ $namaPemesan }}</div>
            </td>
            <td>
                <div class="signature-title">Tanda Tangan Admin</div>
                <div class="signature-line"></div>
                <div class="signature-name">Saung Babakan Cinta</div>
            </td>
        </tr>
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
