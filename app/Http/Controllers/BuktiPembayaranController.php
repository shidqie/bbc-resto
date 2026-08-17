<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class BuktiPembayaranController extends Controller
{
    /** GET /pesan/bayar (cari pesanan untuk bayar) */
    public function cari(Request $request)
    {
        $kodePesanan = $request->query('kode_pesanan');

        if ($kodePesanan) {
            $pesanan = Pesanan::where('id_pesanan', $kodePesanan)->first();
            if ($pesanan) {
                return redirect()->route('pesanan.bayar', $kodePesanan);
            }

            return back()->with('error', 'Pesanan dengan nomor "'.$kodePesanan.'" tidak ditemukan.');
        }

        return view('admin.pos.pembayaran.cari');
    }

    /** GET /pesan/bayar/{kodePesanan} */
    public function show($kodePesanan)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengiriman', 'pembayaran', 'pelanggan'])
            ->where('id_pesanan', $kodePesanan)
            ->first();

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $type = match ($pesanan->jenis_pesanan_id) {
            2 => 'catering',
            3 => 'nasi_box',
            default => 'dine_in',
        };

        $statusBayar = $this->statusBayar($pesanan);
        if ($statusBayar === 'lunas') {
            return view('admin.pos.pembayaran.sukses', compact('pesanan', 'type', 'kodePesanan'));
        }

        return view('admin.pos.pembayaran.index', compact('pesanan', 'type', 'kodePesanan'));
    }

    /** GET /pesan/bayar/status/{kodePesanan} — polling JSON status pembayaran */
    public function statusJson($kodePesanan)
    {
        $pesanan = Pesanan::where('id_pesanan', $kodePesanan)->first();

        if (! $pesanan) {
            return response()->json([
                'lunas' => false,
                'transaction_status' => null,
                'dp_terbayar' => 0,
            ]);
        }

        $dpTerbayar = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima') // Sebagian / Lunas
            ->sum('jumlah_dibayar');
        $lunas = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima')
            ->sum('jumlah_dibayar');

        $transaksi = PaymentTransaction::where('din_number', $kodePesanan)->latest()->first();

        return response()->json([
            'lunas' => $lunas >= (float) $pesanan->total_tagihan || $dpTerbayar >= (float) $pesanan->total_tagihan,
            'transaction_status' => $transaksi?->transaction_status,
            'dp_terbayar' => $dpTerbayar,
        ]);
    }

    /** POST /pesan/bukti */
    public function store(Request $request)
    {
        $request->validate([
            'kode_pesanan' => 'required|string',
            'jenis_pembayaran' => 'required|in:dp,pelunasan',
            'file_bukti' => 'required|file|mimes:jpeg,png,jpg,pdf|max:1024',
        ]);

        $pesanan = Pesanan::where('id_pesanan', $request->kode_pesanan)->first();
        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $total = (float) $pesanan->total_tagihan;
        $dpSudahBayar = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima') // Sebagian / Lunas
            ->sum('jumlah_dibayar');

        $dpPersentase = $pesanan->jenis_pesanan_id == 3 ? 0.25 : 0.5; // Nasi Box = 25%, Catering = 50%

        $jumlahBayar = $request->jenis_pembayaran === 'pelunasan'
            ? max(0, $total - $dpSudahBayar)
            : max(0, $total * $dpPersentase - $dpSudahBayar);

        if ($jumlahBayar <= 0) {
            return back()->with('error', 'Tagihan untuk pembayaran ini sudah lunas.');
        }

        $jenisBayar = $request->jenis_pembayaran === 'pelunasan' ? 'pelunasan' : 'uang_muka';

        $pembayaran = Pembayaran::where('pesanan_id', $pesanan->id)
            ->where('jenis_pembayaran', $jenisBayar)
            ->where('status_verifikasi', 'belum_dibayar')
            ->latest()
            ->first();

        if (!$pembayaran) {
            if ($jenisBayar === 'pelunasan') {
                $pembayaran = Pembayaran::create([
                    'kode_pembayaran' => 'PAY-' . strtoupper(uniqid()),
                    'pesanan_id' => $pesanan->id,
                    'jenis_pembayaran' => 'pelunasan',
                    'metode_pembayaran' => 'transfer_bank',
                    'jumlah_dibayar' => $jumlahBayar,
                    'jumlah_tagihan' => $jumlahBayar,
                    'status_verifikasi' => 'belum_dibayar',
                ]);
            } else {
                return back()->with('error', 'Sesi pembayaran tidak ditemukan atau sudah kedaluwarsa.');
            }
        }

        if ($pembayaran->expires_at && $pembayaran->expires_at < now()) {
            return back()->with('error', 'Sesi pembayaran telah berakhir. Pesanan tidak dapat dilanjutkan atau Anda harus membuat sesi baru.');
        }

        $path = $request->file('file_bukti')->store('bukti-pembayaran', 'public');

        $pembayaran->update([
            'metode_pembayaran' => 'transfer_bank',
            'status_verifikasi' => 'menunggu_verifikasi',
            'tanggal_pembayaran' => now(),
            'bukti_pembayaran' => $path,
            'catatan' => 'Bukti diunggah oleh pemesan',
        ]);

        if ($jenisBayar === 'uang_muka') {
            $pesanan->update(['status_pembayaran_id' => 2]); // Menunggu Verifikasi DP
        } else {
            $pesanan->update(['status_pembayaran_id' => 4]); // Menunggu Verifikasi Pelunasan
        }

        return redirect()->route('pesanan.bayar', $pesanan->id_pesanan)
            ->with('success', 'Bukti pembayaran berhasil dikirim! Kami akan memverifikasi dalam 1×24 jam.');
    }

    /** POST /pesan/bayar/{kodePesanan}/mulai-pelunasan */
    public function mulaiSesiPelunasan(Request $request, $kodePesanan)
    {
        $pesanan = Pesanan::where('id_pesanan', $kodePesanan)->first();
        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        // Pastikan belum kedaluwarsa H-3
        if ($pesanan->batas_pelunasan && $pesanan->batas_pelunasan < now()) {
            return back()->with('error', 'Batas waktu pelunasan telah berakhir. Silakan hubungi admin.');
        }

        // Cek apakah ada sesi pelunasan yang masih aktif
        $aktif = Pembayaran::where('pesanan_id', $pesanan->id)
            ->where('jenis_pembayaran', 'pelunasan')
            ->where('status_verifikasi', 'belum_dibayar')
            ->where('expires_at', '>', now())
            ->exists();

        if ($aktif) {
            return back()->with('error', 'Anda masih memiliki sesi pelunasan yang aktif.');
        }

        $total = (float) $pesanan->total_tagihan;
        $dpSudahBayar = (float) $pesanan->pembayaran()->where('status_verifikasi', 'diterima')->sum('jumlah_dibayar');
        $sisaBayar = max(0, $total - $dpSudahBayar);

        if ($sisaBayar <= 0) {
            return back()->with('error', 'Pesanan sudah lunas.');
        }

        Pembayaran::create([
            'kode_pembayaran' => 'PAY-' . strtoupper(uniqid()),
            'pesanan_id' => $pesanan->id,
            'jenis_pembayaran' => 'pelunasan',
            'metode_pembayaran' => null,
            'jumlah_dibayar' => $sisaBayar,
            'jumlah_tagihan' => $sisaBayar,
            'status_verifikasi' => 'belum_dibayar',
            'expires_at' => now()->addMinutes(15),
        ]);

        return redirect()->route('pesanan.bayar', $pesanan->id_pesanan)->with('success', 'Sesi pelunasan dimulai, selesaikan dalam 15 menit.');
    }

    /** GET /pesan/invoice/{kodePesanan} */
    public function invoicePdf($kodePesanan)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengiriman'])
            ->where('id_pesanan', $kodePesanan)
            ->first();

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $type = match ($pesanan->jenis_pesanan_id) {
            2 => 'catering',
            3 => 'nasi_box',
            default => 'dine_in',
        };

        $namaPemesan = optional($pesanan->pelanggan)->nama
            ?? optional($pesanan->jadwal_pesanan)->nama_penerima
            ?? \App\Models\PesananDinein::find($pesanan->id)?->nama_konsumen
            ?? '-';
        $kontak = optional($pesanan->pelanggan)->nomor_telepon
            ?? optional($pesanan->jadwal_pesanan)->nomor_telepon_penerima
            ?? '-';

        return view('pelanggan.pembayaran.invoice-pdf', compact('pesanan', 'type', 'kodePesanan', 'namaPemesan', 'kontak'));
    }

    /** Status bayar: belum_bayar / dp_terbayar / lunas */
    public function statusBayar(Pesanan $pesanan): string
    {
        $total = (float) $pesanan->total_tagihan;
        $lunas = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima')
            ->sum('jumlah_dibayar');
        $dp = (float) $pesanan->pembayaran()
            ->where('status_verifikasi', 'diterima')
            ->sum('jumlah_dibayar');

        if ($lunas >= $total || $dp >= $total) {
            return 'lunas';
        }
        if ($dp > 0) {
            return 'dp_terbayar';
        }

        return 'belum_bayar';
    }
}
