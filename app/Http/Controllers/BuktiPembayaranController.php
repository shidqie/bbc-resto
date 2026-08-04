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
            $pesanan = Pesanan::where('nomor_pesanan', $kodePesanan)->first();
            if ($pesanan) {
                return redirect()->route('pesanan.bayar', $kodePesanan);
            }

            return back()->with('error', 'Pesanan dengan nomor "'.$kodePesanan.'" tidak ditemukan.');
        }

        return view('pos.pembayaran.cari');
    }

    /** GET /pesan/bayar/{kodePesanan} */
    public function show($kodePesanan)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengantaran', 'pembayaran', 'pelanggan'])
            ->where('nomor_pesanan', $kodePesanan)
            ->first();

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $type = match ($pesanan->jenis_pesanan_id) {
            2 => 'catering',
            3 => 'nasi_box',
            default => 'dine_in',
        };

        $statusBayar = $this->statusBayar($pesanan);
        if ($statusBayar === 'lunas') {
            return view('pos.pembayaran.sukses', compact('pesanan', 'type', 'kodePesanan'));
        }

        return view('pos.pembayaran.index', compact('pesanan', 'type', 'kodePesanan'));
    }

    /** GET /pesan/bayar/status/{kodePesanan} — polling JSON status pembayaran */
    public function statusJson($kodePesanan)
    {
        $pesanan = Pesanan::where('nomor_pesanan', $kodePesanan)->first();

        if (! $pesanan) {
            return response()->json([
                'lunas' => false,
                'transaction_status' => null,
                'dp_terbayar' => 0,
            ]);
        }

        $dpTerbayar = (float) $pesanan->pembayaran()
            ->whereIn('status_pembayaran_id', [2, 3]) // Sebagian / Lunas
            ->sum('jumlah_bayar');
        $lunas = (float) $pesanan->pembayaran()
            ->where('status_pembayaran_id', 3)
            ->sum('jumlah_bayar');

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
            'file_bukti' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $pesanan = Pesanan::where('nomor_pesanan', $request->kode_pesanan)->first();
        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $total = (float) $pesanan->total_tagihan;
        $dpSudahBayar = (float) $pesanan->pembayaran()
            ->whereIn('status_pembayaran_id', [2, 3]) // Sebagian / Lunas
            ->sum('jumlah_bayar');

        $jenisBayarId = $request->jenis_pembayaran === 'pelunasan' ? 3 : 2; // PELUNASAN / UANG_MUKA
        $jumlahBayar = $request->jenis_pembayaran === 'pelunasan'
            ? max(0, $total - $dpSudahBayar)
            : max(0, $total * 0.5 - $dpSudahBayar);

        if ($jumlahBayar <= 0) {
            return back()->with('error', 'Tagihan untuk pembayaran ini sudah lunas.');
        }

        $path = $request->file('file_bukti')->store('bukti-pembayaran', 'public');

        Pembayaran::create([
            'nomor_pembayaran' => 'PAY-'.date('YmdHis').'-'.rand(100, 999),
            'pesanan_id' => $pesanan->id,
            'metode_pembayaran_id' => 3, // Transfer Bank
            'status_pembayaran_id' => 1, // Menunggu
            'jenis_pembayaran_id' => $jenisBayarId,
            'jumlah_bayar' => $jumlahBayar,
            'bukti_pembayaran' => $path,
            'catatan' => 'Bukti diunggah oleh pemesan',
        ]);

        return redirect()->route('pesanan.bayar', $pesanan->nomor_pesanan)
            ->with('success', 'Bukti pembayaran berhasil dikirim! Kami akan memverifikasi dalam 1×24 jam.');
    }

    /** GET /pesan/invoice/{kodePesanan} */
    public function invoicePdf($kodePesanan)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengantaran', 'pembayaran.metode_pembayaran'])
            ->where('nomor_pesanan', $kodePesanan)
            ->first();

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $type = match ($pesanan->jenis_pesanan_id) {
            2 => 'catering',
            3 => 'nasi_box',
            default => 'dine_in',
        };

        return view('pesanan.invoice-pdf', compact('pesanan', 'type', 'kodePesanan'));
    }

    /** Status bayar: belum_bayar / dp_terbayar / lunas */
    public function statusBayar(Pesanan $pesanan): string
    {
        $total = (float) $pesanan->total_tagihan;
        $lunas = (float) $pesanan->pembayaran()
            ->where('status_pembayaran_id', 3)
            ->sum('jumlah_bayar');
        $dp = (float) $pesanan->pembayaran()
            ->whereIn('status_pembayaran_id', [2, 3])
            ->sum('jumlah_bayar');

        if ($lunas >= $total || $dp >= $total) {
            return 'lunas';
        }
        if ($dp > 0) {
            return 'dp_terbayar';
        }

        return 'belum_bayar';
    }
}
