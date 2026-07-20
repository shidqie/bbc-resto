<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $kodePesanan }}</title>
    <style>
        body { font-family: sans-serif; color: #333; line-height: 1.5; font-size: 14px; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #3B82F6; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #3B82F6; font-size: 24px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 14px; }
        
        .info-table { width: 100%; margin-bottom: 30px; }
        .info-table td { vertical-align: top; }
        
        .title { font-weight: bold; font-size: 12px; color: #888; text-transform: uppercase; margin-bottom: 5px; }
        .content { margin-bottom: 15px; font-weight: bold; }
        
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .item-table th { background-color: #f8fafc; border-bottom: 2px solid #e2e8f0; padding: 10px; text-align: left; font-size: 13px; }
        .item-table td { border-bottom: 1px solid #e2e8f0; padding: 10px; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .summary { width: 300px; float: right; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 8px 0; }
        .summary-table .border-top { border-top: 2px solid #e2e8f0; font-weight: bold; font-size: 16px; color: #3B82F6; }
        
        .clearfix::after { content: ""; clear: both; display: table; }
        
        .footer { margin-top: 50px; text-align: center; color: #888; font-size: 12px; border-top: 1px solid #eee; padding-top: 20px; }
    </style>
</head>
<body>

    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td>
                    <h1>SAUNG BABAKAN CINTA</h1>
                    <p>Jl. Raya Babakan Cinta No. 123, Kota Bandung</p>
                    <p>Telp: 0812-3456-7890 | IG: @saungbabakancinta</p>
                </td>
                <td style="text-align: right;">
                    <h2 style="margin:0; font-size: 28px; color:#1e293b;">INVOICE</h2>
                    <p style="margin:5px 0 0; font-weight: bold;">#{{ $kodePesanan }}</p>
                    <p style="margin:5px 0 0;">Tanggal: {{ \Carbon\Carbon::parse($pesanan->created_at)->format('d F Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 50%;">
                <div class="title">Ditagihkan Kepada:</div>
                <div class="content">
                    {{ $pesanan->nama_pemesan }}<br>
                    {{ $pesanan->nomor_wa }}<br>
                    {{ $pesanan->email }}
                </div>
            </td>
            <td style="width: 50%;">
                <div class="title">Detail Acara/Pengiriman:</div>
                <div class="content" style="font-weight: normal;">
                    <strong>Tipe:</strong> {{ $type == 'catering' ? 'Catering' : 'Nasi Box' }}<br>
                    <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pesanan->tanggal_acara)->format('d F Y') }}<br>
                    <strong>Waktu:</strong> {{ $pesanan->waktu_acara ?? '-' }} WIB<br>
                    <strong>Alamat:</strong> {{ $pesanan->alamat_lengkap }}
                </div>
            </td>
        </tr>
    </table>

    <table class="item-table">
        <thead>
            <tr>
                <th>Deskripsi Pesanan</th>
                <th class="text-right">Harga Satuan</th>
                <th class="text-center">Kuantitas</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <strong>{{ $type == 'catering' ? $pesanan->paket->nama_paket : $pesanan->menu->nama }}</strong>
                    @if($type == 'catering')
                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
                            Komponen: 
                            @php
                                $komponenArray = json_decode($pesanan->detail_komponen, true);
                            @endphp
                            @if(is_array($komponenArray))
                                @foreach($komponenArray as $kategori => $item)
                                    {{ $item }}{{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            @endif
                        </div>
                    @endif
                </td>
                <td class="text-right">
                    @php
                        $hargaSatuan = $type == 'catering' ? $pesanan->paket->harga_per_porsi : $pesanan->menu->harga;
                    @endphp
                    Rp {{ number_format($hargaSatuan, 0, ',', '.') }}
                </td>
                <td class="text-center">{{ $pesanan->jumlah_porsi }} Porsi</td>
                <td class="text-right">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <div class="clearfix">
        <div class="summary">
            <table class="summary-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Biaya Pengiriman</td>
                    <td class="text-right">Rp 0</td>
                </tr>
                <tr>
                    <td class="border-top">TOTAL TAGIHAN</td>
                    <td class="border-top text-right">Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
                </tr>
            </table>
            
            <div style="margin-top: 20px; padding: 15px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 5px; font-size: 13px;">
                <div class="title">Status Pembayaran:</div>
                <div style="font-weight: bold; font-size: 16px; color: {{ in_array($pesanan->status, ['menunggu_dp', 'menunggu_konfirmasi']) ? '#eab308' : '#22c55e' }}; text-transform: uppercase;">
                    {{ str_replace('_', ' ', $pesanan->status) }}
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        Terima kasih atas kepercayaan Anda memesan di Saung Babakan Cinta.<br>
        Invoice ini dicetak secara otomatis oleh sistem dan sah sebagai bukti pemesanan.
    </div>

</body>
</html>
