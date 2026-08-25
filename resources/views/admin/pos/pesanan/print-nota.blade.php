<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran Dine-In - {{ $pesanan->id_pesanan ?? ('DIN-' . $pesanan->id) }}</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-saung.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-saung.png') }}">
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: "Courier New", Courier, monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000000;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .preview-toolbar {
            background: #111827;
            color: #ffffff;
            padding: 10px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 12px;
        }
        .toolbar-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-print { background-color: #10b981; color: #ffffff; }
        .btn-print:hover { background-color: #059669; }
        .btn-close { background-color: #374151; color: #ffffff; }
        .btn-close:hover { background-color: #4b5563; }

        .receipt-container {
            display: flex;
            justify-content: center;
            padding: 20px 0;
        }
        .receipt-paper {
            background: #ffffff;
            width: 80mm;
            padding: 4mm;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.1);
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .divider-main {
            text-align: center;
            font-weight: bold;
            letter-spacing: -1px;
            margin: 4px 0;
            overflow: hidden;
            white-space: nowrap;
        }
        .divider-sub {
            text-align: center;
            letter-spacing: -1px;
            margin: 4px 0;
            overflow: hidden;
            white-space: nowrap;
        }

        .resto-name {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            line-height: 1.25;
            margin-bottom: 4px;
        }
        .resto-address {
            font-size: 10px;
            text-align: center;
            line-height: 1.3;
        }
        .resto-phone {
            font-size: 10px;
            text-align: center;
            margin-top: 3px;
        }

        .doc-title {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin: 6px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 4px 0;
        }
        .info-table td {
            padding: 1.5px 0;
            vertical-align: top;
        }
        .info-label { width: 115px; white-space: nowrap; }
        .info-sep { width: 12px; text-align: center; white-space: nowrap; }
        .info-val { font-weight: bold; }

        .items-block {
            margin: 4px 0;
        }
        .item-row {
            margin-bottom: 4px;
        }
        .item-main {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
        }
        .item-sub {
            display: flex;
            justify-content: space-between;
            font-size: 10.5px;
            color: #222222;
            padding-left: 14px;
        }
        .item-note {
            font-size: 10px;
            font-style: italic;
            padding-left: 14px;
            color: #444444;
        }

        .calc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 4px 0;
        }
        .calc-table td {
            padding: 1.5px 0;
        }
        .calc-total {
            font-size: 13px;
            font-weight: bold;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin: 4px 0;
        }
        .payment-table td {
            padding: 1.5px 0;
        }

        .footer-note {
            text-align: center;
            font-size: 10px;
            line-height: 1.35;
            margin-top: 6px;
        }

        @if(request('embed'))
        body {
            background-color: #ffffff;
            padding: 0;
            margin: 0;
        }
        .receipt-container {
            padding: 0;
            display: block;
        }
        .receipt-paper {
            width: 100%;
            padding: 3mm 4mm;
            box-shadow: none;
        }
        @endif

        @media print {
            body {
                background: #ffffff;
                width: 80mm;
                margin: 0;
                padding: 4mm;
            }
            .preview-toolbar {
                display: none !important;
            }
            .receipt-container {
                padding: 0;
                display: block;
            }
            .receipt-paper {
                width: 100%;
                padding: 0;
                box-shadow: none;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    @if(!request('embed'))
    <div class="preview-toolbar no-print">
        <div>
            <strong>Pratinjau Cetak</strong> — Struk Pembayaran Dine-In
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="toolbar-btn btn-close" onclick="window.close()">Tutup</button>
            <button class="toolbar-btn btn-print" onclick="window.print()">🖨️ Cetak</button>
        </div>
    </div>
    @endif

    <div class="receipt-container">
        <div class="receipt-paper">

            {{-- 1. HEADER RUMAH MAKAN --}}
            <div class="resto-name">RUMAH MAKAN SAUNG BABAKAN CINTA</div>
            <div class="resto-address">
                Jl. Ciloa No. KM 6, Pasirhalang,<br>
                Kec. Cisarua, Kabupaten Bandung Barat,<br>
                Jawa Barat
            </div>
            <div class="resto-phone">Telp. 0813-9461-6635</div>

            <div class="divider-main">================================</div>

            {{-- 2. JUDUL DOKUMEN --}}
            <div class="doc-title">STRUK PEMBAYARAN DINE-IN</div>

            {{-- 3. INFORMASI TRANSAKSI --}}
            @php
                $pembayaran = $pesanan->pembayaran ? $pesanan->pembayaran->first() : null;
                $mejaRaw = $pesanan->meja ? ($pesanan->meja->nomor_meja ?? '-') : '-';
                $mejaNum = preg_replace('/[^0-9]/', '', $mejaRaw);
                if (!empty($mejaNum)) {
                    $mejaStr = 'Meja ' . (strlen($mejaNum) === 1 ? '0' . $mejaNum : $mejaNum);
                } else {
                    $mejaStr = str_starts_with($mejaRaw, 'Meja') ? $mejaRaw : ($mejaRaw === '-' ? '-' : 'Meja ' . $mejaRaw);
                }

                $namaKonsumen = $pesanan->nama_konsumen ?? ($pesanan->pelanggan->nama ?? 'Tamu');
                $namaKasir = $pesanan->kasir->nama ?? ($pesanan->pelayan->nama ?? (auth()->check() ? auth()->user()->nama : 'Kasir BBC'));
                $waktuBayar = $pembayaran->dibayar_pada ?? ($pesanan->diperbarui_pada ?? ($pesanan->dibuat_pada ?? now()));
                $tgl = \Carbon\Carbon::parse($waktuBayar)->translatedFormat('d F Y');
                $waktu = \Carbon\Carbon::parse($waktuBayar)->format('H.i') . ' WIB';
            @endphp

            <table class="info-table">
                <tr>
                    <td class="info-label">Kode Pesanan</td>
                    <td class="info-sep">:</td>
                    <td class="info-val">{{ $pesanan->id_pesanan ?? ('DIN-' . $pesanan->id) }}</td>
                </tr>
                <tr>
                    <td class="info-label">Tanggal</td>
                    <td class="info-sep">:</td>
                    <td>{{ $tgl }}</td>
                </tr>
                <tr>
                    <td class="info-label">Waktu</td>
                    <td class="info-sep">:</td>
                    <td>{{ $waktu }}</td>
                </tr>
                <tr>
                    <td class="info-label">Meja</td>
                    <td class="info-sep">:</td>
                    <td class="info-val">{{ $mejaStr }}</td>
                </tr>
                <tr>
                    <td class="info-label">Nama Konsumen</td>
                    <td class="info-sep">:</td>
                    <td class="info-val">{{ $namaKonsumen }}</td>
                </tr>
                <tr>
                    <td class="info-label">Nama Kasir</td>
                    <td class="info-sep">:</td>
                    <td>{{ $namaKasir }}</td>
                </tr>
            </table>

            {{-- 4. RINCIAN PESANAN --}}
            <div class="divider-sub">--------------------------------</div>
            <div class="font-bold text-center" style="margin: 2px 0 4px 0; font-size: 11px;">RINCIAN PESANAN</div>
            <div class="divider-sub">--------------------------------</div>

            <div class="items-block">
                @php
                    $subtotalHitung = 0;
                @endphp
                @foreach($pesanan->detail_pesanan as $item)
                    @php
                        $qty = $item->jumlah ?? $item->qty ?? 1;
                        $hargaSatuan = $item->harga_satuan ?? ($item->menu->harga_jual ?? ($item->menu->harga ?? 0));
                        $sub = $qty * $hargaSatuan;
                        $subtotalHitung += $sub;
                        $namaMenu = $item->menu->nama_menu ?? ($item->menu->nama ?? ($item->nama_menu ?? 'Menu'));
                    @endphp
                    <div class="item-row">
                        <div class="item-main">
                            <span>{{ $qty }}x {{ $namaMenu }}</span>
                        </div>
                        <div class="item-sub">
                            <span>   @ Rp{{ number_format($hargaSatuan, 0, ',', '.') }}</span>
                            <span>Rp{{ number_format($sub, 0, ',', '.') }}</span>
                        </div>
                        @if(!empty($item->catatan))
                            <div class="item-note">   * {{ $item->catatan }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- 5. SUBTOTAL & TOTAL --}}
            @php
                $biayaLayanan = $pesanan->biaya_pelayanan ?? ($pesanan->nominal_biaya_layanan ?? ($pesanan->jumlah_pajak ?? 0));
                $totalTagihan = $pesanan->total_tagihan ?? ($subtotalHitung + $biayaLayanan);
                $uangDiterima = $pembayaran->jumlah_bayar ?? $totalTagihan;
                $kembalian = max(0, $uangDiterima - $totalTagihan);
                $metodeBayarRaw = $pembayaran->metode_pembayaran ?? ($pesanan->metode_bayar ?? 'Tunai');
                
                $metodeBayar = match(strtolower($metodeBayarRaw)) {
                    'cash', 'tunai' => 'Tunai',
                    'qris' => 'QRIS',
                    'transfer', 'bank_transfer' => 'Transfer Bank',
                    'debit', 'kartu_debit' => 'Debit',
                    default => ucfirst($metodeBayarRaw),
                };
            @endphp

            <div class="divider-sub">--------------------------------</div>
            <table class="calc-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rp{{ number_format($subtotalHitung, 0, ',', '.') }}</td>
                </tr>
                @if($biayaLayanan > 0)
                <tr>
                    <td>Biaya Layanan</td>
                    <td class="text-right">Rp{{ number_format($biayaLayanan, 0, ',', '.') }}</td>
                </tr>
                @endif
            </table>

            <div class="divider-main">================================</div>
            <table class="calc-table calc-total">
                <tr>
                    <td>TOTAL</td>
                    <td class="text-right">Rp{{ number_format($totalTagihan, 0, ',', '.') }}</td>
                </tr>
            </table>
            <div class="divider-main">================================</div>

            {{-- 6. PEMBAYARAN --}}
            <table class="payment-table">
                <tr>
                    <td style="width: 140px;">Metode Pembayaran</td>
                    <td style="width: 12px; text-align: center;">:</td>
                    <td class="font-bold">{{ $metodeBayar }}</td>
                </tr>
                <tr>
                    <td>Status Pembayaran</td>
                    <td style="text-align: center;">:</td>
                    <td class="font-bold">LUNAS</td>
                </tr>
            </table>

            <table class="payment-table" style="margin-top: 4px;">
                <tr>
                    <td style="width: 140px;">Bayar</td>
                    <td style="width: 12px; text-align: center;">:</td>
                    <td class="text-right">Rp{{ number_format($uangDiterima, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Kembalian</td>
                    <td style="text-align: center;">:</td>
                    <td class="text-right">Rp{{ number_format($kembalian, 0, ',', '.') }}</td>
                </tr>
            </table>

            <div class="divider-main">================================</div>

            {{-- 7. FOOTER --}}
            <div class="footer-note">
                Terima Kasih Atas Kunjungan Anda<br><br>
                Email : saungbbc996@gmail.com
            </div>

        </div>
    </div>

    @if(request('preview') != '1' && request('auto_print', '1') == '1')
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
    @endif
</body>
</html>