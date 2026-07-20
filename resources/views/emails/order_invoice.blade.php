<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice Pesanan</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <div style="background-color: #3B82F6; color: #ffffff; padding: 20px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">BBC Resto</h1>
            <p style="margin: 5px 0 0; font-size: 14px;">Invoice Pesanan Anda</p>
        </div>
        <div style="padding: 20px;">
            <p>Halo <strong>{{ $pesanan->nama_pemesan }}</strong>,</p>
            <p>Terima kasih telah memesan di BBC Resto. Berikut adalah rincian pesanan Anda:</p>
            
            <div style="background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                <table style="width: 100%; font-size: 14px;">
                    <tr>
                        <td style="padding: 4px 0; color: #6b7280; width: 40%;">Nomor Pesanan</td>
                    @if(isset($pesanan->paket))
                    <tr>
                        <td style="padding: 4px 0; color: #6b7280; width: 40%;">Detail Pesanan</td>
                        <td style="padding: 4px 0; font-weight: bold;">: {{ $pesanan->paket->nama_paket }} ({{ $pesanan->jumlah_porsi }} Porsi)</td>
                    </tr>
                    @elseif(isset($pesanan->menu))
                    <tr>
                        <td style="padding: 4px 0; color: #6b7280; width: 40%;">Detail Pesanan</td>
                        <td style="padding: 4px 0; font-weight: bold;">: {{ $pesanan->menu->nama }} ({{ $pesanan->jumlah_box }} Box)</td>
                    </tr>
                    @endif
                        <td style="padding: 4px 0; font-weight: bold;">: {{ $pesanan->kode_pesanan }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6b7280;">Tanggal Acara</td>
                        <td style="padding: 4px 0; font-weight: bold;">: {{ \Carbon\Carbon::parse($pesanan->tanggal_acara)->format('d F Y') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6b7280; vertical-align: top;">Lokasi / Alamat Acara</td>
                        <td style="padding: 4px 0; font-weight: bold; line-height: 1.4;">: {{ $pesanan->lokasi_acara ?? $pesanan->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6b7280;">Total Tagihan</td>
                        <td style="padding: 4px 0; font-weight: bold; color: #111827;">: Rp {{ number_format($pesanan->total_tagihan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 4px 0; color: #6b7280;">DP yang harus dibayar</td>
                        <td style="padding: 4px 0; font-weight: bold; color: #ef4444;">: Rp {{ number_format($pesanan->dp_amount, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
                <a href="{{ url('/pesan/bayar/' . $pesanan->kode_pesanan) }}" style="background-color: #3B82F6; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: bold; display: inline-block;">Bayar DP Sekarang</a>
            </div>
            
            <p style="font-size: 12px; color: #6b7280;">
                Silakan lakukan pembayaran DP untuk mengonfirmasi pesanan Anda. Jika Anda memiliki pertanyaan, silakan hubungi kami.
            </p>
        </div>
        <div style="background-color: #f9fafb; color: #9ca3af; text-align: center; padding: 15px; font-size: 12px; border-top: 1px solid #e5e7eb;">
            &copy; {{ date('Y') }} BBC Resto. All rights reserved.
        </div>
    </div>
</body>
</html>
