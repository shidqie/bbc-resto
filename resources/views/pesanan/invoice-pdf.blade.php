<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pesanan #{{ $pesanan->nomor_pesanan }}</title>
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
            left: 0px;
            right: 0px;
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
        .item-table th { background-color: #4b7bb2; color: #ffffff; border: 1px solid #3b628e; padding: 5px 8px; text-align: center; font-size: 10.5px; font-weight: bold; }
        .item-table td { border: 1px solid #6b7280; padding: 5px 8px; font-size: 10.5px; color: #111827; }

        .total-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .total-table td { padding: 3px 0; font-size: 11px; }
        .total-grand { font-weight: bold; font-size: 12px; border-top: 1.5px solid #111827; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-success { color: #047857; }
        .text-warning { color: #b45309; }

        .notes-table { margin-top: 15px; font-size: 9.5px; color: #4b5563; border-collapse: collapse; }
        .notes-table td { padding: 1px 0; }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('images/logo-saung.png');
        $logoSrc = '';
        if (file_exists($logoPath)) {
            $logoSrc = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $total = (float) $pesanan->total_tagihan;
        $dpBayar = (float) $pesanan->pembayaran->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
        $lunasBayar = (float) $pesanan->pembayaran->where('status_pembayaran_id', 3)->sum('jumlah_bayar');
        $isLunas = $lunasBayar >= $total || $dpBayar >= $total;
        $detailPesanan = $pesanan->detail_pesanan->first();
    @endphp

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <h1 class="header-title">Bukti Pesanan {{ $type === 'catering' ? 'Catering' : ($type === 'nasi_box' ? 'Nasi Box' : 'Dine In') }}</h1>
                <p class="header-subtitle">Nomor Pesanan : {{ $pesanan->nomor_pesanan }}</p>
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

    {{-- Informasi Pemesan --}}
    <div class="section-title">Informasi Pemesan</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Pemesan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">{{ optional($pesanan->pelanggan)->nama ?? $pesanan->jadwal_pesanan->nama_penerima }}</td>
        </tr>
        <tr>
            <td class="info-label">Kontak</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ optional($pesanan->pelanggan)->nomor_telepon ?? $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '-' }}</td>
        </tr>
        @if($pesanan->jadwal_pesanan)
        <tr>
            <td class="info-label">Tanggal Acara</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('l, d F Y') }}</td>
        </tr>
        @if($pesanan->jadwal_pesanan->alamat_pengantaran)
        <tr>
            <td class="info-label">Lokasi / Alamat</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->jadwal_pesanan->alamat_pengantaran }}</td>
        </tr>
        @endif
        @endif
        <tr>
            <td class="info-label">Status Pesanan</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ match ($pesanan->status_pesanan_id) { 1 => 'Menunggu', 2 => 'Dikonfirmasi', 3 => 'Diproses', 4 => 'Siap', 5 => 'Selesai', 6 => 'Dibatalkan', default => '-' } }}</td>
        </tr>
    </table>

    {{-- Detail Pesanan --}}
    <div class="section-title">Detail Pesanan</div>
    @if($detailPesanan)
    <p style="margin: 4px 0;">
        <strong>Paket:</strong> {{ $detailPesanan->menu->nama_menu ?? '-' }} &times; <strong>{{ $detailPesanan->jumlah }}</strong> Porsi
        <span style="color: #4b5563;">({{ number_format($detailPesanan->harga_satuan, 0, ',', '.') }}/porsi)</span>
    </p>

    @php
        $pilihan = $detailPesanan->pilihan_pesanan_catering ?? collect();
        $detailLain = $pesanan->detail_pesanan->slice(1);
    @endphp

    @if($pilihan->count() > 0 || $detailLain->count() > 0)
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 50%;">Komponen / Menu</th>
                <th style="width: 25%;">Pilihan</th>
                <th style="width: 20%;">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($pilihan as $pil)
            <tr>
                <td class="text-center">{{ $i++ }}</td>
                <td>{{ $pil->komponen_paket->nama_komponen ?? '-' }}</td>
                <td class="font-bold">{{ $pil->pilihan_komponen_paket->nama_pilihan ?? '-' }}</td>
                <td class="text-center">{{ $detailPesanan->jumlah }}</td>
            </tr>
            @endforeach
            @foreach($detailLain as $d)
            <tr>
                <td class="text-center">{{ $i++ }}</td>
                <td class="font-bold">{{ $d->menu->nama_menu ?? '-' }}</td>
                <td>-</td>
                <td class="text-center">{{ $d->jumlah }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endif

    {{-- Rincian Pembayaran --}}
    <div class="section-title">Rincian Pembayaran</div>
    <table class="total-table">
        <tr>
            <td style="width: 70%;">Total Tagihan</td>
            <td class="text-right">Rp {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
        @if($dpBayar > 0)
        <tr>
            <td>Telah Dibayar (Uang Muka)</td>
            <td class="text-right text-success">- Rp {{ number_format($dpBayar, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="total-grand">
            <td>{{ $isLunas ? 'Status' : 'Sisa Pembayaran' }}</td>
            <td class="text-right {{ $isLunas ? 'text-success' : 'text-warning' }}">
                {{ $isLunas ? 'LUNAS' : 'Rp ' . number_format(max(0, $total - $dpBayar), 0, ',', '.') }}
            </td>
        </tr>
    </table>

    {{-- Riwayat Pembayaran --}}
    @if($pesanan->pembayaran->count() > 0)
    <div class="section-title">Riwayat Pembayaran</div>
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 35%;">Nomor Pembayaran</th>
                <th style="width: 15%;">Metode</th>
                <th style="width: 15%;">Jenis</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($pesanan->pembayaran as $pemb)
            <tr>
                <td class="text-center">{{ $i++ }}</td>
                <td>{{ $pemb->nomor_pembayaran }}</td>
                <td>{{ $pemb->metode_pembayaran->nama_metode ?? '-' }}</td>
                <td>{{ $pemb->jenis_pembayaran->nama_jenis ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($pemb->jumlah_bayar, 0, ',', '.') }}</td>
                <td class="text-center">{{ $pemb->status_pembayaran->nama_status ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Keterangan --}}
    <table class="notes-table">
        <tr>
            <td><strong>Keterangan:</strong></td>
        </tr>
        <tr>
            <td>1. Simpan bukti ini sebagai referensi pesanan Anda.</td>
        </tr>
        <tr>
            <td>2. Uang muka (DP) sebesar 50% wajib dilunasi maksimal H-2 sebelum acara.</td>
        </tr>
        <tr>
            <td>3. Bukti pembayaran yang diunggah diverifikasi dalam 1&times;24 jam oleh admin.</td>
        </tr>
    </table>

    {{-- Footer A4 Fixed --}}
    <div class="footer-fixed">
        Saung Babakan Cinta — Rumah Makan Sunda &amp; Catering | Jl. Ciloa No.km 6, Pasirhalang, Kec. Cisarua, KBB, Jawa Barat<br>
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB &bull; Dokumen Bukti Pesanan Pelanggan
    </div>

</body>
</html>
