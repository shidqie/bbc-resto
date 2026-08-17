<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pembayaran - {{ $pesanan->id_pesanan }}</title>
    <style>
        @page {
            size: A4;
            margin: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #111827;
            font-size: 13px;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: #F3F4F6;
        }
        .a4-wrapper {
            position: relative;
            width: 100%;
            max-width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .bg-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
        }
        .container {
            width: 100%;
            padding: 40mm 25mm 30mm 25mm;
            box-sizing: border-box;
            position: relative;
            z-index: 10;
        }
        .header {
            margin-bottom: 40px;
        }
        .header-title {
            font-size: 28px;
            font-weight: 800;
            color: #064E3B; /* Emerald-900 */
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-subtitle {
            font-size: 16px;
            color: #4B5563;
            margin: 0;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .info-grid td {
            vertical-align: top;
            width: 50%;
        }
        .meta-table {
            border-collapse: collapse;
            width: 100%;
        }
        .meta-table td {
            padding: 4px 0;
        }
        .meta-label {
            color: #6B7280;
            font-weight: 600;
            width: 130px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
            padding-bottom: 6px;
            border-bottom: 2px solid #E5E7EB;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #F9FAFB;
            color: #4B5563;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 12px;
            border-bottom: 1px solid #D1D5DB;
            text-align: left;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #E5E7EB;
            color: #111827;
        }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        .font-bold { font-weight: bold; }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .summary-grid td {
            vertical-align: top;
        }
        .payment-info {
            width: 55%;
            padding-right: 30px;
        }
        .payment-info-table td {
            padding: 4px 0;
            font-size: 12px;
        }
        .payment-info-table .label {
            color: #6B7280;
            font-weight: 600;
            width: 130px;
        }
        .totals-table {
            width: 45%;
            border-collapse: collapse;
            background-color: #F9FAFB;
            border-radius: 8px;
        }
        .totals-table td {
            padding: 8px 16px;
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
        .balance-row td {
            border-top: 1px solid #D1D5DB;
            padding-top: 12px;
            font-size: 14px;
        }
        .balance-row .value {
            color: #DC2626;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #6EE7B7;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .alert-box {
            background-color: #FEF2F2;
            border-left: 4px solid #DC2626;
            padding: 12px 16px;
            margin-bottom: 30px;
        }
        .alert-box p {
            margin: 0;
            color: #991B1B;
            font-size: 12px;
            line-height: 1.5;
        }
        
        .footer {
            margin-top: 50px;
            text-align: left;
            color: #4B5563;
            font-size: 12px;
            line-height: 1.6;
        }
        .signature-area {
            margin-top: 40px;
            width: 200px;
            border-top: 1px solid #9CA3AF;
            padding-top: 8px;
            font-weight: bold;
            color: #111827;
        }
        
        .preview-toolbar {
            background-color: #1F2937;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 9999;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .preview-toolbar h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 500;
        }
        .btn-print {
            background-color: #10B981;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            transition: background 0.2s;
        }
        .btn-print:hover {
            background-color: #059669;
        }

        @media print {
            .a4-wrapper { padding: 0; margin: 0; max-width: 100%; min-height: auto; box-shadow: none; overflow: visible; }
            .container { padding: 30mm 20mm; margin: 0; max-width: 100%; min-height: auto; }
            .preview-toolbar { display: none !important; }
            body { background: #fff; }
        }
    </style>
</head>
<body>

    @php
        $dpTerbayar = (float) $pesanan->pembayaran->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $totalTagihan = $pesanan->total_tagihan;
        $dpPersen = $pesanan->persentaseDP();
        $sisaPelunasan = max(0, $totalTagihan - $dpTerbayar);
        
        $pembayaranTerakhir = $pesanan->pembayaran->where('status_verifikasi', 'diterima')->last();
        $tglBayar = $pembayaranTerakhir ? \Carbon\Carbon::parse($pembayaranTerakhir->created_at)->translatedFormat('d F Y') : '-';
        $wktBayar = $pembayaranTerakhir ? \Carbon\Carbon::parse($pembayaranTerakhir->created_at)->format('H.i') . ' WIB' : '-';
        
        $isLunas = $dpTerbayar >= $totalTagihan;
        $statusText = $isLunas ? 'LUNAS' : ($dpTerbayar > 0 ? 'DP TELAH DIBAYAR' : 'BELUM DIBAYAR');
        
        $tglAcaraRaw = $pesanan->jadwal_pesanan ? $pesanan->jadwal_pesanan->tanggal_acara : null;
        $tglAcaraStr = $tglAcaraRaw ? \Carbon\Carbon::parse($tglAcaraRaw)->translatedFormat('d F Y') : '-';
        
        $batasPelunasanStr = '-';
        if($tglAcaraRaw) {
            $batasPelunasanStr = \Carbon\Carbon::parse($tglAcaraRaw)->subDays(4)->translatedFormat('d F Y');
        }
        
        $bgPath = public_path('images/buktipembayaran.png');
        $bgBase64 = base64_encode(file_get_contents($bgPath));
        $bgSrc = 'data:image/png;base64,' . $bgBase64;
    @endphp

    <div class="preview-toolbar">
        <h3>Preview Bukti Pembayaran</h3>
        <button class="btn-print" onclick="window.print()">
            <svg style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Unduh PDF / Cetak
        </button>
    </div>

    <div class="a4-wrapper">
        <img src="{{ $bgSrc }}" class="bg-image" />
    
        <div class="container">
        
        <div class="header">
            <h1 class="header-title">SAUNG BABAKAN CINTA</h1>
            <p class="header-subtitle">{{ $isLunas ? 'Bukti Pembayaran Lunas' : 'Bukti Pembayaran DP' }}</p>
        </div>
        
        <table class="info-grid">
            <tr>
                <td>
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">Kode Pesanan</td>
                            <td class="font-bold">: {{ $pesanan->id_pesanan }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Tanggal Pesanan</td>
                            <td>: {{ \Carbon\Carbon::parse($pesanan->dibuat_pada)->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Jenis Pesanan</td>
                            <td>: {{ $type === 'nasi_box' ? 'Nasi Box' : ($type === 'catering' ? 'Katering' : 'Resto / Dine In') }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="meta-table">
                        <tr>
                            <td class="meta-label">Nama Pemesan</td>
                            <td class="font-bold">: {{ $namaPemesan }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Tanggal Acara</td>
                            <td>: {{ $tglAcaraStr }}</td>
                        </tr>
                        <tr>
                            <td class="meta-label">Status</td>
                            <td>: <span class="status-badge">{{ $statusText }}</span></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div class="section-title">Rincian Pemesanan</div>
        
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Deskripsi Layanan</th>
                    <th class="text-right" style="width: 25%;">Harga Satuan</th>
                    <th class="text-right" style="width: 25%;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pesanan->detail_pesanan as $detail)
                    @php
                        $hargaSatuan = $detail->harga_satuan ?? optional($detail->menu)->harga_jual ?? 0;
                        $amountLine = $detail->jumlah * $hargaSatuan;
                    @endphp
                    <tr>
                        <td>
                            <div class="font-bold">{{ $detail->menu->nama_menu ?? 'Paket Menu' }}</div>
                            <div style="font-size: 11px; color: #6B7280; margin-top: 4px;">Kuantitas: {{ $detail->jumlah }} {{ $type === 'nasi_box' ? 'Box' : 'Porsi' }}</div>
                        </td>
                        <td class="text-right">Rp {{ number_format($hargaSatuan, 0, ',', '.') }}</td>
                        <td class="text-right font-bold">Rp {{ number_format($amountLine, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="summary-grid">
            <tr>
                <td class="payment-info">
                    <div class="section-title">Informasi Pembayaran</div>
                    <table class="payment-info-table">
                        <tr>
                            <td class="label">Metode Pembayaran</td>
                            <td>: Transfer Bank</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal Bayar</td>
                            <td>: {{ $tglBayar }}</td>
                        </tr>
                        <tr>
                            <td class="label">Waktu Bayar</td>
                            <td>: {{ $wktBayar }}</td>
                        </tr>
                    </table>
                </td>
                <td>
                    <table class="totals-table">
                        <tr>
                            <td class="label">Total Pesanan</td>
                            <td class="value">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="label">{{ $isLunas ? 'Total Dibayar' : 'Telah Dibayar (DP '.$dpPersen.'%)' }}</td>
                            <td class="value" style="color: #059669;">Rp {{ number_format($dpTerbayar, 0, ',', '.') }}</td>
                        </tr>
                        @if(!$isLunas)
                        <tr class="balance-row">
                            <td class="label">Sisa Pelunasan</td>
                            <td class="value font-bold">Rp {{ number_format($sisaPelunasan, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>

        @if(!$isLunas && $dpTerbayar > 0)
        <div class="alert-box">
            <p><strong>Penting:</strong> Sisa pelunasan sebesar <strong>Rp {{ number_format($sisaPelunasan, 0, ',', '.') }}</strong> wajib dibayarkan selambat-lambatnya pada <strong>{{ $batasPelunasanStr }} (H-4)</strong> sebelum tanggal acara. Kegagalan melakukan pelunasan akan mengakibatkan pesanan dibatalkan secara otomatis.</p>
        </div>
        @endif
        
        <div class="footer">
            <p>Pembayaran {{ $isLunas ? 'telah lunas' : 'DP' }} telah kami terima dengan baik. Terima kasih atas kepercayaan Anda memilih layanan Saung Babakan Cinta.<br>Silakan simpan dokumen ini sebagai bukti transaksi dan pemesanan yang sah.</p>
            
            <div class="signature-area">
                Saung Babakan Cinta
            </div>
        </div>

    </div>
    </div>

</body>
</html>
