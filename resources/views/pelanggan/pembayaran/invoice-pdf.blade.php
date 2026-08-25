@php
    \Carbon\Carbon::setLocale('id');

    $logoPath = public_path('images/logo-saung.png');
    $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';

    $fontRegularPath = public_path('fonts/Outfit-Regular.ttf');
    $fontBoldPath = public_path('fonts/Outfit-Bold.ttf');

    $outfitRegular = file_exists($fontRegularPath) ? base64_encode(file_get_contents($fontRegularPath)) : '';
    $outfitBold = file_exists($fontBoldPath) ? base64_encode(file_get_contents($fontBoldPath)) : $outfitRegular;

    $namaPemesan = $namaPemesan ?? optional($pesanan->pelanggan)->nama
        ?? optional($pesanan->jadwal_pesanan)->nama_penerima
        ?? optional($pesanan->pengiriman)->nama_penerima
        ?? \App\Models\PesananDinein::find($pesanan->id)?->nama_konsumen
        ?? '-';
    $kontak = $kontak ?? optional($pesanan->pelanggan)->nomor_telepon
        ?? optional($pesanan->jadwal_pesanan)->nomor_telepon_penerima
        ?? optional($pesanan->pengiriman)->nomor_telepon_penerima
        ?? '-';
    $emailPemesan = optional($pesanan->pelanggan)->email;
    $type = $type ?? (match ($pesanan->jenis_pesanan_id) { 2 => 'catering', 3 => 'nasi_box', default => 'dine_in' });
    $layananTitle = $type === 'nasi_box' ? 'BUKTI PEMESANAN NASI BOX' : ($type === 'catering' ? 'BUKTI PEMESANAN KATERING' : 'BUKTI PEMESANAN');

    $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
    $totalTagihan = (float) $pesanan->total_tagihan;
    $dpPersen = $pesanan->persentaseDP();
    $isLunas = $dpTerbayar >= $totalTagihan;
    $sisaPembayaran = max(0, $totalTagihan - $dpTerbayar);

    $statusText = $isLunas ? 'LUNAS' : ($dpTerbayar > 0 ? 'DP TERVERIFIKASI' : 'MENUNGGU PEMBAYARAN');
    $statusClass = $isLunas ? 'status-lunas' : ($dpTerbayar > 0 ? 'status-dp' : 'status-pending');

    $pembayaranTerakhir = $pesanan->pembayaran->where('status_verifikasi', 'diterima')->last() 
        ?? $pesanan->pembayaran->last();
    $tglBayar = $pembayaranTerakhir ? \Carbon\Carbon::parse($pembayaranTerakhir->tanggal_pembayaran ?? $pembayaranTerakhir->created_at)->locale('id')->translatedFormat('d F Y') : '-';
    $wktBayar = $pembayaranTerakhir ? \Carbon\Carbon::parse($pembayaranTerakhir->tanggal_pembayaran ?? $pembayaranTerakhir->created_at)->format('H:i') . ' WIB' : '-';

    $metodeBayarList = $pesanan->pembayaran
        ->where('status_verifikasi', 'diterima')
        ->filter(fn($p) => !empty($p->metode_pembayaran))
        ->map(function($p) {
            return match(strtolower($p->metode_pembayaran)) {
                'transfer_bank', 'transfer' => 'Transfer Bank',
                'qris' => 'QRIS',
                'tunai', 'cash' => 'Tunai',
                default => ucwords(str_replace('_', ' ', $p->metode_pembayaran))
            };
        })
        ->unique()
        ->values();

    if ($metodeBayarList->isEmpty()) {
        $metodeBayarList = $pesanan->pembayaran
            ->filter(fn($p) => !empty($p->metode_pembayaran))
            ->map(function($p) {
                return match(strtolower($p->metode_pembayaran)) {
                    'transfer_bank', 'transfer' => 'Transfer Bank',
                    'qris' => 'QRIS',
                    'tunai', 'cash' => 'Tunai',
                    default => ucwords(str_replace('_', ' ', $p->metode_pembayaran))
                };
            })
            ->unique()
            ->values();
    }

    $metodeBayarFormatted = $metodeBayarList->isNotEmpty() 
        ? $metodeBayarList->join(', ')
        : (match(strtolower($pesanan->metode_pembayaran ?? '')) {
            'transfer_bank', 'transfer' => 'Transfer Bank',
            'qris' => 'QRIS',
            'tunai', 'cash' => 'Tunai',
            default => (!empty($pesanan->metode_pembayaran) ? ucwords(str_replace('_', ' ', $pesanan->metode_pembayaran)) : 'Transfer Bank')
        });

    $tglAcaraRaw = $pesanan->jadwal_pesanan ? $pesanan->jadwal_pesanan->tanggal_acara : null;
    $tglAcaraStr = $tglAcaraRaw ? \Carbon\Carbon::parse($tglAcaraRaw)->locale('id')->translatedFormat('d F Y') : '-';
    
    $waktuAcaraRaw = optional($pesanan->jadwal_pesanan)->waktu_pengiriman 
        ?? optional($pesanan->jadwal_pesanan)->waktu_acara 
        ?? ($tglAcaraRaw && strlen($tglAcaraRaw) > 10 ? \Carbon\Carbon::parse($tglAcaraRaw)->format('H:i') : null);
    $waktuAcaraStr = $waktuAcaraRaw ? (\Carbon\Carbon::parse($waktuAcaraRaw)->format('H:i') . ' WIB') : '-';

    $batasPelunasanStr = '-';
    if($tglAcaraRaw) {
        $batasPelunasanStr = \Carbon\Carbon::parse($tglAcaraRaw)->subDays(3)->locale('id')->translatedFormat('d F Y');
    }

    $tglPesanStr = \Carbon\Carbon::parse($pesanan->dibuat_pada ?? $pesanan->created_at)->locale('id')->translatedFormat('d F Y');
    $tglCetakStr = \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y');

    $ongkirNominal = (float) ($pesanan->ongkir ?? optional($pesanan->pengiriman)->biaya_pengiriman ?? 0);
    $alamatRaw = optional($pesanan->pengiriman)->alamat_pengiriman ?? optional($pesanan->jadwal_pesanan)->alamat_pengiriman ?? '';
    $isPickupAddress = in_array(strtolower(trim($alamatRaw)), ['-', 'diambil di toko (pickup)', 'diambil di resto (pickup)', 'pickup', 'ambil_sendiri', 'diambil']);

    $rawMetodeKirim = strtolower($pesanan->metode_pengiriman ?? optional($pesanan->pengiriman)->metode_pengiriman ?? '');
    $isDelivery = in_array($rawMetodeKirim, ['delivery', 'diantar', 'kurir'])
        || !empty($pesanan->pengiriman)
        || $ongkirNominal > 0
        || (!empty($alamatRaw) && !$isPickupAddress);

    $metodeKirim = $isDelivery ? 'Diantar' : 'Diambil di Resto';
    $alamatKirim = $isDelivery 
        ? ($alamatRaw ?: (optional($pesanan->pelanggan)->alamat ?: '-'))
        : 'Diambil di Resto';
    $catatanPesanan = $pesanan->catatan ?? optional($pesanan->jadwal_pesanan)->catatan ?? null;

    $statusPesananText = optional($pesanan->status_pesanan)->nama_status 
        ?? match($pesanan->status_pesanan_id) {
            1 => 'Menunggu Konfirmasi',
            2 => 'Pesanan Dikonfirmasi',
            3, 4 => 'Sedang Diproses',
            5 => 'Siap Dikirim / Diambil',
            6 => 'Selesai',
            default => 'Pesanan Dikonfirmasi'
        };
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pemesanan - {{ $pesanan->id_pesanan }}</title>
    <style>
        @if($outfitRegular)
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: normal;
            src: url('data:font/truetype;charset=utf-8;base64,{{ $outfitRegular }}') format('truetype');
        }
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 400;
            src: url('data:font/truetype;charset=utf-8;base64,{{ $outfitRegular }}') format('truetype');
        }
        @endif

        @if($outfitBold)
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: bold;
            src: url('data:font/truetype;charset=utf-8;base64,{{ $outfitBold }}') format('truetype');
        }
        @font-face {
            font-family: 'Outfit';
            font-style: normal;
            font-weight: 700;
            src: url('data:font/truetype;charset=utf-8;base64,{{ $outfitBold }}') format('truetype');
        }
        @endif

        @page {
            size: A4 portrait;
            margin: 18mm 20mm 18mm 20mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Outfit', sans-serif !important;
        }

        html, body, table, td, th, tr, div, span, p, b, strong, small, h1, h2, h3, a, label, tbody, thead, tfoot {
            font-family: 'Outfit', sans-serif !important;
        }

        body {
            font-family: 'Outfit', sans-serif !important;
            font-size: 8.5pt;
            color: #1f2937;
            background: #ffffff;
            line-height: 1.4;
            padding: 18mm 20mm 18mm 20mm;
        }

        @media print {
            body {
                padding: 0;
            }
        }

        /* ─── HEADER KOP ─── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .brand-title {
            font-size: 12.5pt;
            font-weight: bold;
            color: #0D3024;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 2px;
            line-height: 1.2;
        }
        .brand-address {
            font-size: 7.8pt;
            color: #4b5563;
            line-height: 1.35;
        }

        .divider-line {
            border-bottom: 2px solid #0D3024;
            margin-bottom: 12px;
        }

        /* ─── JUDUL DOKUMEN TENGAH ─── */
        .doc-title-centered {
            text-align: center;
            margin-bottom: 14px;
        }
        .doc-title-main {
            font-size: 11.5pt;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .doc-meta-main {
            font-size: 8pt;
            color: #4b5563;
            margin-bottom: 5px;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #111827;
            color: #111827;
            background: #ffffff;
            border-radius: 2px;
        }
        .status-lunas {
            border-color: #0D3024;
            color: #0D3024;
            background-color: #f2f7f4;
        }
        .status-dp {
            border-color: #1E40AF;
            color: #1E40AF;
            background-color: #eff6ff;
        }
        .status-pending {
            border-color: #92400E;
            color: #92400E;
            background-color: #fefce8;
        }

        /* ─── DATA PEMESANAN (2 KOLOM) ─── */
        .section-title {
            font-size: 8.2pt;
            font-weight: bold;
            color: #0D3024;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .info-grid > tbody > tr > td {
            vertical-align: top;
            width: 50%;
        }
        .info-grid > tbody > tr > td:first-child {
            padding-right: 14px;
        }
        .info-grid > tbody > tr > td:last-child {
            padding-left: 14px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table td {
            padding: 2px 0;
            font-size: 8pt;
            vertical-align: top;
            line-height: 1.35;
        }
        .data-label {
            width: 125px;
            color: #4b5563;
        }
        .data-colon {
            width: 10px;
            text-align: center;
            color: #9ca3af;
        }
        .data-value {
            color: #111827;
            font-weight: bold;
        }

        /* ─── TABEL RINCIAN PEMESANAN ─── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 12px;
            font-size: 8pt;
        }
        .items-table th {
            background-color: #0D3024;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            letter-spacing: 0.5px;
            padding: 5px 8px;
            text-align: left;
            border: 1px solid #0D3024;
        }
        .items-table td {
            padding: 5px 8px;
            border: 1px solid #e5e7eb;
            vertical-align: middle;
        }
        .items-table tbody tr:nth-child(even) {
            background-color: #fafafa;
        }
        .item-name {
            font-weight: bold;
            color: #111827;
            font-size: 8.2pt;
        }
        .item-desc {
            font-size: 7.2pt;
            color: #4b5563;
            font-style: italic;
            margin-top: 2px;
            line-height: 1.3;
        }
        .table-total-row td {
            background-color: #f9fafb;
            border: 1px solid #d1d5db;
            font-size: 8.2pt;
            padding: 6px 8px;
        }

        /* ─── RINCIAN PEMBAYARAN (2 KOLOM) ─── */
        .payment-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .payment-grid > tbody > tr > td {
            vertical-align: top;
            width: 50%;
        }
        .payment-grid > tbody > tr > td:first-child {
            padding-right: 14px;
        }
        .payment-grid > tbody > tr > td:last-child {
            padding-left: 14px;
        }

        /* ─── KETENTUAN & CATATAN (PALING BAWAH) ─── */
        .notes-box {
            margin-top: 4px;
            margin-bottom: 12px;
            padding: 6px 10px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
        }
        .notes-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #0D3024;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .notes-list {
            font-size: 7.2pt;
            color: #4b5563;
            line-height: 1.4;
        }
        .notes-list p {
            margin-bottom: 2px;
        }
        .notes-list p:last-child {
            margin-bottom: 0;
        }

        /* ─── FOOTER ─── */
        .doc-footer {
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            font-size: 7pt;
            color: #6b7280;
            display: table;
            width: 100%;
            line-height: 1.35;
        }
        .doc-footer-left {
            display: table-cell;
            text-align: left;
            font-weight: bold;
            color: #374151;
            width: 40%;
            vertical-align: top;
        }
        .doc-footer-right {
            display: table-cell;
            text-align: right;
            font-style: italic;
            width: 60%;
            vertical-align: top;
        }

        /* ─── UTILS ─── */
        .text-center { text-align: center !important; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: bold !important; }
        .nowrap { white-space: nowrap !important; }
    </style>
</head>
<body>

    {{-- ── HEADER KOP RESTORAN ── --}}
    <table class="header-table">
        <tr>
            @if($logoBase64)
            <td style="width: 52px; padding-right: 12px;">
                <img src="{{ $logoBase64 }}" style="width: 48px; height: auto;" alt="Logo Saung Babakan Cinta" />
            </td>
            @endif
            <td>
                <div class="brand-title">RUMAH MAKAN SAUNG BABAKAN CINTA</div>
                <div class="brand-address">
                    Jl. Ciloa No. KM 6, Pasirhalang, Kec. Cisarua, Kabupaten Bandung Barat, Jawa Barat 40551
                </div>
            </td>
        </tr>
    </table>

    <div class="divider-line"></div>

    {{-- ── JUDUL DOKUMEN DI TENGAH ── --}}
    <div class="doc-title-centered">
        <div class="doc-title-main">{{ $layananTitle }}</div>
        <div class="doc-meta-main">
            No. Pesanan : <strong>{{ $pesanan->id_pesanan }}</strong> &bull; Tanggal Pemesanan : {{ $tglPesanStr }}
        </div>
        <div>
            <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
        </div>
    </div>

    {{-- ── DATA PEMESANAN (2 KOLOM) ── --}}
    <div class="section-title">Data Pemesanan</div>

    <table class="info-grid">
        <tr>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="data-label">Nama Pemesan</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">{{ $namaPemesan }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">No. Telepon</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">{{ $kontak }}</td>
                    </tr>
                    @if($emailPemesan)
                    <tr>
                        <td class="data-label">Email</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">{{ $emailPemesan }}</td>
                    </tr>
                    @endif
                    @if($catatanPesanan)
                    <tr>
                        <td class="data-label">Catatan Pesanan</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">{{ $catatanPesanan }}</td>
                    </tr>
                    @endif
                </table>
            </td>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="data-label">Tanggal Acara</td>
                        <td class="data-colon">:</td>
                        <td class="data-value nowrap">{{ $tglAcaraStr }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Waktu Acara</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">{{ $waktuAcaraStr }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Metode Pengiriman</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">{{ $metodeKirim }}</td>
                    </tr>
                    @if($isDelivery && $alamatKirim !== '-')
                    <tr>
                        <td class="data-label">Alamat Pengiriman</td>
                        <td class="data-colon">:</td>
                        <td class="data-value" style="word-break: break-word;">{{ $alamatKirim }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    {{-- ── RINCIAN PEMESANAN ── --}}
    <div class="section-title">Rincian Pemesanan</div>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">NO.</th>
                <th style="width: 47%;">PESANAN</th>
                <th style="width: 14%;" class="text-center">JUMLAH</th>
                <th style="width: 16%;" class="text-right">HARGA SATUAN</th>
                <th style="width: 18%;" class="text-right">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $subtotalItems = 0;
            @endphp
            @foreach($pesanan->detail_pesanan as $detail)
                @php
                    $qty = (int) $detail->jumlah;
                    $hargaSatuan = (float) ($detail->harga_satuan ?? optional($detail->menu)->harga_jual ?? 0);
                    $lineTotal = $qty * $hargaSatuan;
                    $subtotalItems += $lineTotal;

                    $komponenList = [];
                    if ($detail->pilihan_pesanan_catering && $detail->pilihan_pesanan_catering->count() > 0) {
                        foreach ($detail->pilihan_pesanan_catering as $pilihan) {
                            $namaKomp = optional($pilihan->komponen_paket)->nama_komponen;
                            $namaPilihan = optional($pilihan->pilihan_komponen_paket)->nama_pilihan;
                            if ($namaPilihan) {
                                $komponenList[] = ($namaKomp ? $namaKomp . ': ' : '') . $namaPilihan;
                            }
                        }
                    }
                @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td>
                        <div class="item-name">{{ $detail->menu->nama_menu ?? 'Paket Menu' }}</div>
                        @if(count($komponenList) > 0)
                            <div class="item-desc">Item Pilihan: {{ implode(' • ', $komponenList) }}</div>
                        @endif
                    </td>
                    <td class="text-center">{{ $qty }} {{ $type === 'nasi_box' ? 'Box' : 'Porsi' }}</td>
                    <td class="text-right nowrap">Rp{{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                    <td class="text-right font-bold nowrap">Rp{{ number_format($lineTotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach

            @if($ongkirNominal > 0)
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>
                    <div class="item-name">Biaya Pengiriman</div>
                    @if(optional($pesanan->pengiriman)->jarak_pengiriman)
                        <div class="item-desc">Estimasi Jarak: {{ optional($pesanan->pengiriman)->jarak_pengiriman }} km</div>
                    @endif
                </td>
                <td class="text-center">-</td>
                <td class="text-right">-</td>
                <td class="text-right font-bold nowrap">Rp{{ number_format($ongkirNominal, 0, ',', '.') }}</td>
            </tr>
            @else
            <tr>
                <td class="text-center">{{ $no++ }}</td>
                <td>
                    <div class="item-name">Biaya Pengiriman</div>
                </td>
                <td class="text-center">-</td>
                <td class="text-right">-</td>
                <td class="text-right font-bold nowrap">Rp0</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr class="table-total-row">
                <td colspan="4" class="text-right font-bold">TOTAL PESANAN</td>
                <td class="text-right font-bold nowrap">Rp{{ number_format($totalTagihan, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- ── RINCIAN PEMBAYARAN (2 KOLOM) ── --}}
    <div class="section-title">Rincian Pembayaran</div>

    <table class="payment-grid">
        <tr>
            <td>
                <table class="data-table">
                    <tr>
                        <td class="data-label">Total Pesanan</td>
                        <td class="data-colon">:</td>
                        <td class="data-value nowrap">Rp{{ number_format($totalTagihan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">DP Terbayar</td>
                        <td class="data-colon">:</td>
                        <td class="data-value nowrap">Rp{{ number_format($dpTerbayar, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Sisa Pembayaran</td>
                        <td class="data-colon">:</td>
                        <td class="data-value nowrap" style="{{ $sisaPembayaran > 0 ? 'color: #991b1b;' : '' }}">Rp{{ number_format($sisaPembayaran, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table class="data-table">
                    @if($tglAcaraRaw && !$isLunas)
                    <tr>
                        <td class="data-label">Batas Pelunasan</td>
                        <td class="data-colon">:</td>
                        <td class="data-value nowrap">{{ $batasPelunasanStr }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="data-label">Metode Pembayaran</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">{{ $metodeBayarFormatted }}</td>
                    </tr>
                    <tr>
                        <td class="data-label">Status Pembayaran</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">
                            @if($isLunas)
                                Lunas Terverifikasi
                            @elseif($dpTerbayar > 0)
                                DP Terverifikasi
                            @else
                                Menunggu Pembayaran
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="data-label">Status Pesanan</td>
                        <td class="data-colon">:</td>
                        <td class="data-value">{{ $statusPesananText }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ── KETENTUAN & CATATAN (PALING BAWAH) ── --}}
    <div class="notes-box">
        <div class="notes-title">Ketentuan & Catatan:</div>
        <div class="notes-list">
            <p>• Dokumen ini merupakan bukti resmi pemesanan dari Rumah Makan Saung Babakan Cinta.</p>
            <p>• Harga dan dokumen ini menjadi referensi pengambilan atau serah terima pesanan.</p>
            <p>• Uang muka (DP) yang telah disetorkan tidak dapat dikembalikan apabila terjadi pembatalan oleh pemesan.</p>
        </div>
    </div>

    {{-- ── FOOTER ── --}}
    <div class="doc-footer">
        <div class="doc-footer-left">Rumah Makan Saung Babakan Cinta</div>
        <div class="doc-footer-right">
            Bukti ini merupakan tanda bahwa pesanan katering telah tercatat pada sistem.<br>
            Harap simpan dokumen ini dengan baik sebagai bukti pemesanan.
        </div>
    </div>

</body>
</html>
