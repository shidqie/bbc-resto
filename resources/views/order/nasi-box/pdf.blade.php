<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rincian Pesanan Nasi Box #{{ $pesanan->nomor_pesanan }}</title>
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
            position: relative;
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
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        
        .bg-watermark {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -100;
            opacity: 0.15;
        }

        .notes-table { margin-top: 15px; font-size: 9.5px; color: #4b5563; border-collapse: collapse; }
        .notes-table td { padding: 1px 0; }
    </style>
</head>
<body>

    @php
        // Load logo as base64 for dompdf
        $logoPath = public_path('images/logo-saung.png');
        $logoSrc = '';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoSrc = 'data:image/png;base64,' . $logoData;
        }

        // Load background as base64
        $bgPath = public_path('images/pdf.svg');
        $bgSrc = '';
        if (file_exists($bgPath)) {
            $bgData = base64_encode(file_get_contents($bgPath));
            $bgSrc = 'data:image/svg+xml;base64,' . $bgData;
        }
    @endphp

    @if($bgSrc)
    <img src="{{ $bgSrc }}" class="bg-watermark" />
    @endif

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <h1 class="header-title">Rincian Pesanan Nasi Box (Internal)</h1>
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

    {{-- Informasi Pemesan & Pengiriman --}}
    <div class="section-title">Informasi Pesanan & Pengiriman (Tim Pengantaran)</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Pemesan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">{{ $pesanan->jadwal_pesanan->nama_penerima ?? '-' }} ({{ $pesanan->jadwal_pesanan->nomor_telepon_penerima ?? '-' }})</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal Acara</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->translatedFormat('l, d F Y') }}</td>
        </tr>
        <tr>
            <td class="info-label">Waktu Acara</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->jadwal_pesanan->waktu_pengantaran ?: '-' }} WIB</td>
        </tr>
        <tr>
            <td class="info-label">Alamat / Lokasi</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->jadwal_pesanan->alamat_pengantaran ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Jumlah Pesanan</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->detail_pesanan->first()->jumlah ?? 0 }} Box</td>
        </tr>
        <tr>
            <td class="info-label">Paket Dipilih</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold text-blue-600">{{ $pesanan->detail_pesanan->first()->menu->nama_menu ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Catatan Tambahan</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->catatan ?? '-' }}</td>
        </tr>
    </table>

    {{-- Detail Persiapan --}}
    <div class="section-title">Detail Persiapan (Tim Dapur)</div>
    @if($pesanan->detail_pesanan && $pesanan->detail_pesanan->count() > 0)
    <div style="margin-top: 8px; font-size: 11px;">
        <p style="margin: 0 0 4px 0;"><strong>Paket:</strong> {{ $pesanan->detail_pesanan->first()->menu->nama_menu ?? '-' }}</p>
        <p style="margin: 0 0 8px 0;"><strong>Rincian Item:</strong></p>
        <ul style="margin: 0 0 12px 15px; padding: 0;">
            @foreach($pesanan->detail_pesanan as $detail)
            <li style="margin-bottom: 3px;">
                {{ $detail->jumlah }} x <strong>{{ $detail->menu->nama_menu ?? '-' }}</strong>
            </li>
            @endforeach
        </ul>
    </div>
    @else
    <p style="font-size: 11px; margin-top: 5px; color: #4b5563;">Tidak ada rincian menu yang tersimpan.</p>
    @endif

    {{-- Kebutuhan Bahan --}}
    @if(isset($kebutuhanBahan) && count($kebutuhanBahan) > 0)
    <div class="section-title">Kebutuhan Bahan Baku (Resep)</div>
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 75%;">Nama Bahan Baku</th>
                <th style="width: 20%;">Total Kebutuhan</th>
            </tr>
        </thead>
        <tbody>
            @php $i = 1; @endphp
            @foreach($kebutuhanBahan as $bahan)
            <tr>
                <td class="text-center">{{ $i++ }}</td>
                <td class="font-bold">{{ $bahan['nama_bahan'] }}</td>
                <td class="text-center">{{ $bahan['total_kebutuhan'] }} {{ $bahan['satuan'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Keterangan / Note --}}
    <table class="notes-table">
        <tr>
            <td><strong>Perhatian Tim Dapur & Pengantaran:</strong></td>
        </tr>
        <tr>
            <td>1. Pastikan jumlah pesanan (<strong>{{ $pesanan->detail_pesanan->first()->jumlah ?? 0 }} Box</strong>) dan lauk-pauknya sesuai.</td>
        </tr>
        <tr>
            <td>2. Periksa kembali catatan tambahan dari pemesan (jika ada).</td>
        </tr>
    </table>

    {{-- Footer A4 Fixed --}}
    <div class="footer-fixed">
        Saung Babakan Cinta — Rumah Makan Sunda & Catering | Jl. Ciloa No.km 6, Pasirhalang, Kec. Cisarua, KBB, Jawa Barat<br>
        Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }} WIB • Dokumen Internal Tim Dapur & Pengantaran
    </div>

</body>
</html>
