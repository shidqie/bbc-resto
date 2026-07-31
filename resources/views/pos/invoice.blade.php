<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>E-Receipt #{{ $kodePesanan }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 25mm 15mm;
        }
        body { 
            font-family: Helvetica, Arial, sans-serif; 
            color: #1f2937; 
            line-height: 1.4; 
            font-size: 11px; 
            margin: 0; 
            padding: 0;
        }
        .footer-fixed {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            height: 30px;
            border-top: 1px solid #d1d5db;
            text-align: center;
            font-size: 8.5px;
            color: #6b7280;
            padding-top: 6px;
        }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .header-title { font-size: 16px; font-weight: bold; margin: 0; color: #111827; }
        .header-subtitle { font-size: 11px; margin: 3px 0 0; color: #4b5563; font-weight: bold; }
        .logo { width: 110px; text-align: right; vertical-align: top; }
        .logo img { max-width: 70px; height: auto; }
        .logo-text { font-size: 10px; font-weight: bold; color: #1f2937; margin-top: 3px; }
        .divider { border-bottom: 1.5px solid #1f2937; margin-bottom: 15px; }
        .section-title { font-weight: bold; font-size: 11px; color: #111827; margin-top: 14px; margin-bottom: 4px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .info-table td { padding: 1.5px 0; vertical-align: top; font-size: 11px; }
        .info-label { width: 180px; color: #374151; }
        .info-colon { width: 12px; text-align: center; }
        .info-val { color: #111827; }
        .item-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 6px; }
        .item-table th { background-color: #1f2937; color: #ffffff; border: 1px solid #111827; padding: 5px 8px; text-align: center; font-size: 10.5px; font-weight: bold; }
        .item-table td { border: 1px solid #d1d5db; padding: 5px 8px; font-size: 10.5px; color: #111827; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .notes-table { margin-top: 15px; font-size: 9.5px; color: #4b5563; border-collapse: collapse; }
        .notes-table td { padding: 1px 0; }
        .badge-lunas { display: inline-block; background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 10px; }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logo-saung.png');
        $logoSrc = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        $pembayaranTerakhir = $pesanan->pembayaran ? $pesanan->pembayaran->last() : null;
        $metode = $pembayaranTerakhir ? strtoupper($pembayaranTerakhir->metode_pembayaran ?? 'CASH') : 'CASH';
        $jumlahBayar = $pembayaranTerakhir ? $pembayaranTerakhir->jumlah_bayar : $pesanan->total_harga;
        $kembalian = max(0, $jumlahBayar - $pesanan->total_harga);
    @endphp

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <h1 class="header-title">E-Receipt Dine-In</h1>
                <p class="header-subtitle">No. Pesanan: {{ $kodePesanan }}</p>
            </td>
            <td class="logo">
                @if($logoSrc)
                    <img src="{{ $logoSrc }}" alt="Logo" />
                @else
                    <svg width="45" height="45" viewBox="0 0 100 100">
                        <polygon points="50,10 90,90 10,90" fill="#2d3748" />
                    </svg>
                @endif
                <div class="logo-text">Saung Babakan Cinta</div>
            </td>
        </tr>
    </table>

    <div class="divider"></div>

    {{-- Detail Transaksi --}}
    <div class="section-title">Detail Transaksi</div>
    <table class="info-table">
        <tr>
            <td class="info-label">No. Pesanan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">{{ $pesanan->no_pesanan }}</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal &amp; Waktu</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->tanggal_pesanan ? \Carbon\Carbon::parse($pesanan->tanggal_pesanan)->format('d-m-Y H:i') : date('d-m-Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="info-label">Jenis Layanan</td>
            <td class="info-colon">:</td>
            <td class="info-val">Dine-In{{ $pesanan->no_meja ? ' — Meja ' . $pesanan->no_meja : '' }}</td>
        </tr>
        <tr>
            <td class="info-label">Pelanggan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">{{ $pesanan->nama_pelanggan ?? 'Pelanggan Umum' }}</td>
        </tr>
        <tr>
            <td class="info-label">Status Pesanan</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="text-transform: capitalize;">{{ str_replace('_', ' ', $pesanan->status_pesanan ?? '-') }}</td>
        </tr>
    </table>

    {{-- Detail Pembayaran --}}
    <div class="section-title">Detail Pembayaran</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Metode Pembayaran</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">{{ $metode }}</td>
        </tr>
        <tr>
            <td class="info-label">Status Pembayaran</td>
            <td class="info-colon">:</td>
            <td class="info-val">
                <span class="badge-lunas">{{ strtoupper($pesanan->status_pembayaran ?? 'LUNAS') }}</span>
            </td>
        </tr>
        <tr>
            <td class="info-label">Total Tagihan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
        </tr>
        @if($jumlahBayar > 0)
        <tr>
            <td class="info-label">Jumlah Dibayar</td>
            <td class="info-colon">:</td>
            <td class="info-val">Rp {{ number_format($jumlahBayar, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($kembalian > 0)
        <tr>
            <td class="info-label">Kembalian</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold" style="color: #059669;">Rp {{ number_format($kembalian, 0, ',', '.') }}</td>
        </tr>
        @endif
    </table>

    {{-- Rincian Menu --}}
    <div class="section-title">Rincian Pesanan</div>
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 45%; text-align: left;">Nama Menu</th>
                <th style="width: 15%;">Qty</th>
                <th style="width: 20%;">Harga Satuan</th>
                <th style="width: 20%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pesanan->details as $d)
            <tr>
                <td>{{ $d->menu->nama ?? 'Item Menu' }}</td>
                <td class="text-center">{{ $d->jumlah }}</td>
                <td class="text-right">Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($d->subtotal ?? ($d->jumlah * $d->harga_satuan), 0, ',', '.') }}</td>
            </tr>
            @if($d->catatan)
            <tr>
                <td colspan="4" style="font-size: 9px; color: #6b7280; padding-left: 12px;">*Catatan: {{ $d->catatan }}</td>
            </tr>
            @endif
            @empty
            <tr>
                <td colspan="4" class="text-center" style="color: #9ca3af;">Tidak ada rincian item.</td>
            </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right font-bold" style="border-top: 1px solid #374151;">TOTAL</td>
                <td class="text-right font-bold" style="border-top: 1px solid #374151;">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- Keterangan --}}
    <table class="notes-table">
        <tr>
            <td><strong>Keterangan:</strong></td>
        </tr>
        <tr>
            <td>1. Dokumen ini sah dikeluarkan secara otomatis oleh sistem Saung Babakan Cinta.</td>
        </tr>
        <tr>
            <td>2. Harap simpan E-Receipt ini sebagai bukti transaksi Anda.</td>
        </tr>
        <tr>
            <td>3. Terima kasih telah makan di Saung Babakan Cinta!</td>
        </tr>
    </table>

    {{-- Footer Fixed --}}
    <div class="footer-fixed">
        Saung Babakan Cinta — Rumah Makan Sunda &amp; Catering | Jl. Ciloa No.km 6, Pasirhalang, Kec. Cisarua, KBB, Jawa Barat | WA: +62 813-9461-6635<br>
        E-Receipt diterbitkan otomatis pada {{ date('d-m-Y H:i') }} WIB
    </div>

</body>
</html>
