<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $kodePesanan }}</title>
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

        $dpPaid = isset($pesanan->dp_amount) && $pesanan->dp_amount > 0;
        $isDpOnly = $dpPaid && $pesanan->dp_amount < $pesanan->total_tagihan && !in_array($pesanan->status_bayar, ['lunas', 'paid']);
        $metodePembayaran = $isDpOnly ? 'Uang Muka (DP 50%)' : 'Lunas (100%)';
    @endphp

    @if($bgSrc)
    <img src="{{ $bgSrc }}" class="bg-watermark" />
    @endif

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <h1 class="header-title">Rincian Pesanan {{ $type == 'catering' ? 'Katering' : 'Nasi Box' }}</h1>
                <p class="header-subtitle">Nomor Tagihan : {{ $kodePesanan }}</p>
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

    {{-- Detail Pemesanan --}}
    <div class="section-title">Detail Pemesanan</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Kode Pemesanan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">{{ $kodePesanan }}</td>
        </tr>
        <tr>
            <td class="info-label">Tipe Layanan</td>
            <td class="info-colon">:</td>
            <td class="info-val">Layanan {{ $type == 'catering' ? 'Katering' : 'Nasi Box' }}</td>
        </tr>
        <tr>
            <td class="info-label">Jumlah {{ $type == 'catering' ? 'Porsi' : 'Box' }}</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->jumlah_porsi ?? $pesanan->jumlah_box ?? 0 }} {{ $type == 'catering' ? 'Porsi' : 'Box' }}</td>
        </tr>
        <tr>
            <td class="info-label">Tanggal Acara</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ \Carbon\Carbon::parse($pesanan->tanggal_acara)->format('d-m-Y') }}</td>
        </tr>
    </table>

    {{-- Detail Pembayaran --}}
    <div class="section-title">Detail Pembayaran</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Total Tagihan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="info-label">Metode Pembayaran</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $metodePembayaran }}</td>
        </tr>
        <tr>
            <td class="info-label">Status Pembayaran</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="text-transform: capitalize; font-weight: bold; color: {{ in_array($pesanan->status_bayar, ['lunas', 'paid']) ? '#16a34a' : ($pesanan->status_bayar === 'dp_terbayar' ? '#2563eb' : '#d97706') }};">
                {{ str_replace('_', ' ', $pesanan->status_bayar ?? 'Belum Bayar') }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Status Pesanan</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="text-transform: capitalize;">{{ str_replace('_', ' ', $pesanan->status) }}</td>
        </tr>
        @if($isDpOnly)
        <tr>
            <td class="info-label">DP Terbayar</td>
            <td class="info-colon">:</td>
            <td class="info-val text-emerald-600">Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="info-label">Sisa Tagihan (Pelunasan)</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold" style="color: #dc2626;">Rp {{ number_format($pesanan->total_tagihan - $pesanan->dp_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="info-label">Batas Pelunasan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold" style="color: #d97706;">{{ \Carbon\Carbon::parse($pesanan->tanggal_acara)->subDays(2)->format('d-m-Y') }} (H-2 Acara)</td>
        </tr>
        @elseif(in_array($pesanan->status_bayar, ['lunas', 'paid']))
        <tr>
            <td class="info-label">Tanggal Pelunasan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold" style="color: #16a34a;">{{ \Carbon\Carbon::parse($pesanan->diperbarui_pada)->format('d-m-Y H:i') }} WIB</td>
        </tr>
        @endif
    </table>

    {{-- Detail Pemesan & Lokasi --}}
    <div class="section-title">Detail Pemesan & Lokasi</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Pemesan</td>
            <td class="info-colon">:</td>
            <td class="info-val font-bold">{{ $pesanan->nama_pemesan }}</td>
        </tr>
        <tr>
            <td class="info-label">No HP / No. Telp</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->kontak ?? $pesanan->nomor_wa ?? $pesanan->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Alamat / Venue Acara</td>
            <td class="info-colon">:</td>
            <td class="info-val">{{ $pesanan->lokasi_acara ?? $pesanan->alamat_lengkap ?? '-' }}</td>
        </tr>
        @if(isset($pesanan->metode_pengiriman))
        <tr>
            <td class="info-label">Metode Pengiriman</td>
            <td class="info-colon">:</td>
            <td class="info-val" style="text-transform: capitalize;">{{ $pesanan->metode_pengiriman }}</td>
        </tr>
        @endif
    </table>

    {{-- Rincian Pesanan Table --}}
    <div class="section-title">Rincian Pesanan</div>
    <table class="item-table">
        <thead>
            <tr>
                <th style="width: 45%;">Nama Paket & Detail Menu</th>
                <th style="width: 15%;">Jumlah</th>
                <th style="width: 20%;">Harga Satuan</th>
                <th style="width: 20%;">Jumlah Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $namaItem = $type == 'catering' 
                    ? ($pesanan->paket->nama_paket ?? 'Paket Katering') 
                    : ($pesanan->paket->nama_paket ?? 'Menu Nasi Box');
                $qty = $pesanan->jumlah_porsi ?? $pesanan->jumlah_box ?? 1;
                $ongkir = $pesanan->ongkos_kirim ?? 0;
                $subtotalPaket = max(0, $pesanan->total_tagihan - $ongkir);
                $hargaSatuan = $qty > 0 ? round($subtotalPaket / $qty) : $subtotalPaket;
            @endphp
            <tr>
                <td>
                    <strong>{{ $namaItem }}</strong>
                    @if(isset($pesanan->details) && count($pesanan->details) > 0)
                        <div style="font-size: 9.5px; color: #4b5563; margin-top: 4px;">
                            <strong>Komponen Menu:</strong>
                            <ul style="margin: 2px 0 0 12px; padding: 0;">
                                @foreach($pesanan->details as $d)
                                    <li>{{ $d->menu->nama ?? '-' }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </td>
                <td class="text-center">{{ $qty }} {{ $type == 'catering' ? 'Porsi' : 'Box' }}</td>
                <td class="text-right">Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($subtotalPaket, 0, ',', '.') }}</td>
            </tr>
            @if($ongkir > 0)
            <tr>
                <td colspan="3" class="text-right">Ongkos Kirim (Delivery Fee)</td>
                <td class="text-right">Rp {{ number_format($ongkir, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right font-bold">Total Tagihan Keseluruhan</td>
                <td class="text-right font-bold">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
            </tr>
            @if($isDpOnly)
            <tr>
                <td colspan="3" class="text-right text-emerald-600">DP Terbayar (50%)</td>
                <td class="text-right text-emerald-600">- Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right font-bold" style="color: #dc2626;">Sisa Pembayaran (Pelunasan)</td>
                <td class="text-right font-bold" style="color: #dc2626;">Rp {{ number_format($pesanan->total_tagihan - $pesanan->dp_amount, 0, ',', '.') }}</td>
            </tr>
            @elseif(in_array($pesanan->status_bayar, ['lunas', 'paid']))
            <tr>
                <td colspan="3" class="text-right font-bold" style="color: #16a34a;">Telah Dibayar (LUNAS)</td>
                <td class="text-right font-bold" style="color: #16a34a;">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    {{-- Keterangan / Note --}}
    <table class="notes-table">
        <tr>
            <td><strong>Keterangan:</strong></td>
        </tr>
        <tr>
            <td>1. Dokumen ini sah dikeluarkan secara otomatis oleh sistem Saung Babakan Cinta.</td>
        </tr>
        <tr>
            <td>2. Harap simpan bukti pembayaran/invoice ini sebagai dokumen sah pemesanan Anda.</td>
        </tr>
        <tr>
            <td>3. Jika membutuhkan bantuan lebih lanjut, silakan hubungi kontak resmi Saung Babakan Cinta.</td>
        </tr>
    </table>

    {{-- Footer A4 Fixed --}}
    <div class="footer-fixed">
        Saung Babakan Cinta — Rumah Makan Sunda & Katering | Jl. Ciloa No.km 6, Pasirhalang, Kec. Cisarua, KBB, Jawa Barat | WA: +62 813-9461-6635<br>
        Dokumen ini diterbitkan secara otomatis pada {{ date('d-m-Y H:i') }} WIB • Bukti Pemesanan Resmi
    </div>

</body>
</html>
