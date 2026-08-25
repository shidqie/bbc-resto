<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Dapur Checker - {{ $pesanan->id_pesanan ?? ('DIN-' . $pesanan->id) }}</title>
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

        .doc-title {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin: 4px 0 2px 0;
        }
        .badge-tambahan {
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
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
            margin-bottom: 3px;
            font-size: 11px;
            line-height: 1.3;
        }
        .item-main {
            font-weight: bold;
        }
        .item-note {
            font-size: 10px;
            font-style: italic;
            padding-left: 18px;
            color: #333333;
        }

        .catatan-block {
            margin: 6px 0 4px 0;
            font-size: 10.5px;
        }
        .catatan-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

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

    <div class="preview-toolbar no-print">
        <div>
            <strong>Pratinjau Cetak</strong> — Struk Dapur Checker
        </div>
        <div style="display: flex; gap: 8px;">
            <button class="toolbar-btn btn-close" onclick="window.close()">Tutup</button>
            <button class="toolbar-btn btn-print" onclick="window.print()">🖨️ Cetak</button>
        </div>
    </div>

    <div class="receipt-container">
        <div class="receipt-paper">

            {{-- 1. JUDUL DOKUMEN --}}
            <div class="doc-title">STRUK DAPUR CHECKER</div>

            @php
                $hasTambahan = false;
                if ($pesanan->detail_pesanan) {
                    $hasTambahan = $pesanan->detail_pesanan->contains('is_tambahan', true);
                }
            @endphp

            @if($hasTambahan)
                <div class="badge-tambahan">PESANAN TAMBAHAN</div>
            @endif

            <div class="divider-main">================================</div>

            {{-- 2. INFORMASI PESANAN --}}
            @php
                $mejaRaw = $pesanan->meja ? ($pesanan->meja->nomor_meja ?? '-') : '-';
                $mejaNum = preg_replace('/[^0-9]/', '', $mejaRaw);
                if (!empty($mejaNum)) {
                    $mejaStr = 'Meja ' . (strlen($mejaNum) === 1 ? '0' . $mejaNum : $mejaNum);
                } else {
                    $mejaStr = str_starts_with($mejaRaw, 'Meja') ? $mejaRaw : ($mejaRaw === '-' ? '-' : 'Meja ' . $mejaRaw);
                }

                $namaKonsumen = $pesanan->nama_konsumen ?? ($pesanan->pelanggan->nama ?? 'Tamu');
                $namaKasir = $pesanan->kasir->nama ?? ($pesanan->pelayan->nama ?? (auth()->check() ? auth()->user()->nama : 'Kasir BBC'));
                $waktuStr = \Carbon\Carbon::parse($pesanan->dibuat_pada ?? now())->format('H.i') . ' WIB';
            @endphp

            <table class="info-table">
                <tr>
                    <td class="info-label">Kode Pesanan</td>
                    <td class="info-sep">:</td>
                    <td class="info-val">{{ $pesanan->id_pesanan ?? ('DIN-' . $pesanan->id) }}</td>
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
                <tr>
                    <td class="info-label">Waktu</td>
                    <td class="info-sep">:</td>
                    <td>{{ $waktuStr }}</td>
                </tr>
            </table>

            <div class="divider-main">================================</div>

            {{-- 3. RINCIAN PESANAN --}}
            <div class="font-bold text-left" style="margin: 4px 0 2px 0; font-size: 11px;">RINCIAN PESANAN</div>
            <div class="divider-sub">--------------------------------</div>

            <div class="items-block">
                @php
                    $allCatatan = [];
                @endphp
                @foreach($pesanan->detail_pesanan as $item)
                    @php
                        $qty = $item->jumlah ?? $item->qty ?? 1;
                        $namaMenu = $item->menu->nama_menu ?? ($item->menu->nama ?? ($item->nama_menu ?? 'Menu'));
                        if (!empty($item->catatan)) {
                            $allCatatan[] = $item->catatan;
                        }
                    @endphp
                    <div class="item-row">
                        <div class="item-main">{{ $qty }}x  {{ $namaMenu }}</div>
                        @if(!empty($item->catatan))
                            <div class="item-note">* {{ $item->catatan }}</div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="divider-sub">--------------------------------</div>

            {{-- 4. CATATAN KESELURUHAN (JIKA ADA) --}}
            @if(count($allCatatan) > 0)
                <div class="catatan-block">
                    <div class="catatan-title">Catatan:</div>
                    @foreach($allCatatan as $cat)
                        <div>{{ $cat }}</div>
                    @endforeach
                </div>
            @endif

            <div class="divider-main">================================</div>

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