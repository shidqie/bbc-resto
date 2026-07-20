<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bukti Pembayaran Diterima</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background-color: #10B981; color: #ffffff; padding: 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">Pembayaran Berhasil!</h1>
            <p style="margin: 5px 0 0; font-size: 14px;">DP Pesanan Anda Telah Kami Terima</p>
        </div>
        <div style="padding: 20px;">
            <p>Halo <strong>{{ $pesanan->nama_pemesan }}</strong>,</p>
            <p>Kabar gembira! Pembayaran DP (Down Payment) Anda untuk pesanan <strong>{{ $pesanan->kode_pesanan }}</strong> telah berhasil diverifikasi oleh sistem kami.</p>
            
            <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                <table style="width: 100%; font-size: 14px;">
                    <tr>
                        <td style="padding: 4px 0; color: #065f46; width: 40%;">Status Pesanan</td>
                    @if(isset($pesanan->paket))
                    <tr>
                        <td style="padding: 4px 0; color: #065f46; width: 40%;">Detail Pesanan</td>
                        <td style="padding: 4px 0; font-weight: bold;">: {{ $pesanan->paket->nama_paket }} ({{ $pesanan->jumlah_porsi }} Porsi)</td>
                    </tr>
                    @elseif(isset($pesanan->menu))
                    <tr>
                        <td style="padding: 4px 0; color: #065f46; width: 40%;">Detail Pesanan</td>
                        <td style="padding: 4px 0; font-weight: bold;">: {{ $pesanan->menu->nama }} ({{ $pesanan->jumlah_box }} Box)</td>
                    </tr>
                    @endif
                        <td style="padding: 4px 0; font-weight: bold; color: #059669;">: TERKONFIRMASI (Sedang Diproses)</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #065f46; vertical-align: top;">Lokasi / Alamat Acara</td>
                        <td style="padding: 4px 0; font-weight: bold; line-height: 1.4;">: {{ $pesanan->lokasi_acara ?? $pesanan->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #065f46;">Nominal Dibayar</td>
                        <td style="padding: 4px 0; font-weight: bold;">: Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #065f46;">Sisa Pelunasan</td>
                        <td style="padding: 4px 0; font-weight: bold; color: #ef4444;">: Rp {{ number_format($pesanan->total_tagihan - $pesanan->dp_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            
            <p style="font-size: 14px; color: #4b5563;">
                Sisa tagihan (pelunasan) dapat dibayarkan H-1 sebelum acara atau sesuai kesepakatan. Tim dapur kami sudah mulai mempersiapkan pesanan Anda.
            </p>
            
            <div style="text-align: center; margin-top: 30px; margin-bottom: 10px;">
                <a href="{{ url('/pesan/status/' . $pesanan->kode_pesanan) }}" style="background-color: #10B981; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">Cek Status Pesanan</a>
            </div>
        </div>
        <div style="background-color: #f9fafb; color: #9ca3af; text-align: center; padding: 15px; font-size: 12px; border-top: 1px solid #e5e7eb;">
            &copy; {{ date('Y') }} BBC Resto. All rights reserved.
        </div>
    </div>
</body>
</html>
