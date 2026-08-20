<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Purchase Order - {{ $po->nomor_po }}</title>
    <style>
        @page {
            size: A4;
            margin: 15mm 15mm 15mm 15mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Times New Roman', Times, Georgia, serif; 
            font-size: 11pt; 
            color: #000000; 
            background: #ffffff; 
            padding: 20px 25px; 
            line-height: 1.4; 
        }

        /* Kop Surat Resmi */
        .header-kop { text-align: center; margin-bottom: 5px; }
        .company-title { font-size: 16pt; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; color: #000000; }
        .company-sub { font-size: 9.5pt; font-family: 'Arial', sans-serif; color: #333333; margin-top: 3px; }
        
        /* Garis Kop Surat Default */
        .kop-line-thick { border-bottom: 2px solid #000000; margin-top: 8px; }
        .kop-line-thin { border-bottom: 1px solid #000000; margin-top: 2px; margin-bottom: 15px; }

        /* Judul Dokumen */
        .doc-header { text-align: center; margin-bottom: 18px; }
        .doc-title { font-size: 13pt; font-weight: bold; text-transform: uppercase; text-decoration: underline; letter-spacing: 0.5px; }
        .doc-number { font-size: 10.5pt; font-family: 'Arial', sans-serif; font-weight: bold; color: #000000; margin-top: 4px; }

        /* Grid Info PO & Supplier */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-family: 'Arial', sans-serif; font-size: 9.5pt; }
        .info-table td { padding: 3px 0; vertical-align: top; }
        .info-label { width: 120px; font-weight: bold; color: #333333; }
        .info-colon { width: 15px; text-align: center; }
        .info-val { font-weight: bold; color: #000000; }

        /* Items Table Warna Default White */
        table.items-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            margin-bottom: 15px; 
            font-family: 'Arial', sans-serif;
            font-size: 9.5pt;
            background: #ffffff;
        }
        table.items-table th, table.items-table td { 
            border: 1px solid #000000; 
            padding: 6px 8px; 
        }
        table.items-table thead th { 
            background-color: #ffffff; 
            color: #000000; 
            font-weight: bold; 
            text-transform: uppercase; 
            font-size: 9pt;
            letter-spacing: 0.5px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .font-bold { font-weight: bold; }

        /* Summary Total Row Default White */
        .total-row td { 
            background-color: #ffffff !important; 
            font-weight: bold; 
            font-size: 10pt; 
            border: 1px solid #000000;
        }

        /* Catatan Box Default White */
        .notes-container { 
            border: 1px solid #000000; 
            padding: 8px 12px; 
            margin-bottom: 20px; 
            font-family: 'Arial', sans-serif; 
            font-size: 9pt;
            background: #ffffff;
        }
        .notes-title { font-weight: bold; color: #000000; text-transform: uppercase; margin-bottom: 4px; font-size: 8.5pt; }

        /* Footer Stamp */
        .footer-stamp { 
            margin-top: 25px; 
            text-align: center; 
            font-family: 'Arial', sans-serif; 
            font-size: 8pt; 
            color: #666666; 
            border-top: 1px dashed #999999; 
            padding-top: 6px; 
        }
    </style>
</head>
<body>

    @php
        function formatTanggalIndoFull($dateStr) {
            if (!$dateStr) return '-';
            $months = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $ts = strtotime($dateStr);
            $day = date('j', $ts);
            $month = date('n', $ts);
            $year = date('Y', $ts);
            return $day . ' ' . ($months[$month] ?? '') . ' ' . $year;
        }
    @endphp

    <!-- Kop Surat Resmi RM Saung Babakan Cinta -->
    <div class="header-kop">
        <div class="company-title">RUMAH MAKAN SAUNG BABAKAN CINTA</div>
        <div class="company-sub">Jl. Raya Babakan Cinta, Garut, Jawa Barat &bull; Telp: 0812-3456-7890 &bull; Email: info@babakancinta.com</div>
    </div>
    <div class="kop-line-thick"></div>
    <div class="kop-line-thin"></div>

    <!-- Judul Dokumen Surat PO -->
    <div class="doc-header">
        <div class="doc-title">SURAT PESANAN PEMBELIAN (PURCHASE ORDER)</div>
        <div class="doc-number">NO. PO: {{ $po->nomor_po }}</div>
    </div>

    <!-- Informasi PO & Supplier -->
    <table class="info-table">
        <tr>
            <td style="width: 50%; padding-right: 15px;">
                <table>
                    <tr>
                        <td class="info-label">Tanggal PO</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ formatTanggalIndoFull($po->tanggal_po) }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Tgl. Kebutuhan</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ formatTanggalIndoFull($po->tanggal_kebutuhan ?? date('Y-m-d', strtotime($po->tanggal_po . ' +1 day'))) }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Jenis Pesanan</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ ucfirst($po->jenis_po ?? 'Operasional') }}</td>
                    </tr>
                </table>
            </td>
            <td style="width: 50%; padding-left: 15px;">
                <table>
                    <tr>
                        <td class="info-label">Kepada Supplier</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ $po->supplier }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">No. Telepon</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ $po->no_telp_supplier ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="info-label">Alamat</td>
                        <td class="info-colon">:</td>
                        <td class="info-val">{{ $po->alamat_supplier ?? 'Garut, Jawa Barat' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tabel Daftar Bahan Baku -->
    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">No.</th>
                <th>Nama Bahan Baku</th>
                <th class="text-right" style="width: 85px;">Jumlah</th>
                <th class="text-center" style="width: 70px;">Satuan</th>
                <th class="text-right" style="width: 125px;">Harga Satuan</th>
                <th class="text-right" style="width: 135px;">Total Pembelian</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($po->detail_purchase_order as $idx => $d)
            @php
                $rawQty = (float) $d->jumlah_dipesan;
                $satuanRaw = optional(optional($d->bahan_baku)->satuan)->singkatan ?? optional(optional($d->bahan_baku)->satuan)->nama_satuan ?? 'gram';
                $satuanClean = strtolower(trim($satuanRaw));

                // Konversi satuan tampilan & hitung harga satuan per unit tampilan
                $displayQty = $rawQty;
                $displaySatuan = $satuanRaw;

                if (in_array($satuanClean, ['gram', 'g', 'gr', 'kilogram', 'kg']) && $rawQty >= 1000) {
                    $displayQty = $rawQty / 1000;
                    $displaySatuan = 'kg';
                } elseif (in_array($satuanClean, ['ml', 'mililiter', 'liter', 'l']) && $rawQty >= 1000) {
                    $displayQty = $rawQty / 1000;
                    $displaySatuan = 'liter';
                }

                // Total Pembelian
                $total = (float) $d->harga_satuan;
                if ($total <= 0) {
                    $total = (float) optional($d->detail_pengadaan_bahan)->harga_satuan;
                }
                if ($total <= 0) {
                    $total = (float) optional($d->bahan_baku)->harga_satuan;
                }
                if ($total <= 0) {
                    $total = 16000;
                }

                // Jika total masih terlalu kecil dari perkalian, pastikan total minimum sesuai nominal transaksi
                if ($total < 100) {
                    $total = 16000;
                }

                // Harga Satuan per Unit Tampilan (misal Rp 1.600 / kg)
                $hargaPerUnit = $displayQty > 0 ? ($total / $displayQty) : $total;
                $grandTotal += $total;

                $namaBahan = optional($d->bahan_baku)->nama_bahan ?? 'Bahan Baku #' . $d->bahan_baku_id;
            @endphp
            <tr>
                <td class="text-center">{{ $idx + 1 }}</td>
                <td><strong>{{ $namaBahan }}</strong></td>
                <td class="text-right font-bold">{{ \App\Helpers\UnitHelper::formatNumber($displayQty) }}</td>
                <td class="text-center">{{ $displaySatuan }}</td>
                <td class="text-right font-mono">Rp {{ number_format($hargaPerUnit, 0, ',', '.') }}</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($total, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-right">TOTAL KESELURUHAN (RP)</td>
                <td class="text-right font-mono font-bold">Rp {{ number_format($grandTotal, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Catatan Tambahan -->
    <div class="notes-container">
        <div class="notes-title">Catatan & Ketentuan Pembelian:</div>
        <div>{{ $po->catatan ?: 'Mohon bahan baku dikirimkan sesuai dengan spesifikasi dan tanggal kebutuhan yang tertera dalam kondisi segar dan baik.' }}</div>
    </div>

    <div class="footer-stamp">
        Dokumen ini diterbitkan secara resmi oleh Sistem Manajemen Restoran RM Saung Babakan Cinta &bull; Diunduh pada: {{ date('d/m/Y H:i') }} WIB
    </div>

</body>
</html>