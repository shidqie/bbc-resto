<?php

namespace App\Http\Controllers;

use App\Models\BuktiPembayaran;
use App\Models\PesananCatering;
use App\Models\PesananNasiBox;
use Illuminate\Http\Request;

class BuktiPembayaranController extends Controller
{
    /** GET /pesan/bayar/{kodePesanan} */
    public function show($kodePesanan)
    {
        // Cari di kedua tabel
        $pesanan = PesananCatering::where('kode_pesanan', $kodePesanan)->first();
        $type = 'catering';

        if (!$pesanan) {
            $pesanan = PesananNasiBox::where('kode_pesanan', $kodePesanan)->first();
            $type = 'nasi_box';
        }

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        if (in_array($pesanan->status_bayar, ['lunas', 'paid'])) {
            return redirect()->route('lacak.index', ['kode_pesanan' => $kodePesanan])
                ->with('success', 'Pesanan ini sudah lunas.');
        }

        // Generate/Refresh Snap Token untuk pembayaran (DP atau Pelunasan)
        if (in_array($pesanan->status, ['ditinjau', 'dikonfirmasi', 'terkonfirmasi', 'menunggu_pelunasan'])) {
            $pesanan->snap_token = \App\Http\Controllers\MidtransController::generateSnapToken($pesanan, $type);
        }

        return view('pesanan.bayar', compact('pesanan', 'type', 'kodePesanan'));
    }

    /** POST /pesan/bukti */
    public function store(Request $request)
    {
        $request->validate([
            'kode_pesanan'     => 'required|string',
            'jenis_pembayaran' => 'required|in:dp,pelunasan',
            'file_bukti'       => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        // Cari pesanan
        $pesanan = PesananCatering::where('kode_pesanan', $request->kode_pesanan)->first();
        $type = PesananCatering::class;

        if (!$pesanan) {
            $pesanan = PesananNasiBox::where('kode_pesanan', $request->kode_pesanan)->first();
            $type = PesananNasiBox::class;
        }

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $path = $request->file('file_bukti')->store('bukti-pembayaran', 'public');

        BuktiPembayaran::create([
            'pesanan_id'       => $pesanan->id,
            'pesanan_type'     => $type,
            'jenis_pembayaran' => $request->jenis_pembayaran,
            'file_path'        => $path,
            'status'           => 'menunggu_verifikasi',
        ]);

        // Update status pesanan
        $pesanan->update(['status' => 'menunggu_konfirmasi']);


        return redirect()->route('pesanan.bayar', $request->kode_pesanan)
            ->with('success', 'Bukti pembayaran berhasil dikirim! Kami akan memverifikasi dalam 1×24 jam.');
    }


    public function invoicePdf($kodePesanan)
    {
        $pesanan = \App\Models\PesananCatering::with('buktiPembayarans', 'paket', 'details.menu')
            ->where('kode_pesanan', $kodePesanan)->first();
        $type = 'catering';

        if (!$pesanan) {
            $pesanan = \App\Models\PesananNasiBox::with('buktiPembayarans', 'paket', 'details.menu')
                ->where('kode_pesanan', $kodePesanan)->first();
            $type = 'nasi_box';
        }

        // Fallback: cari pesanan dine-in (Pesanan reguler)
        if (!$pesanan) {
            $pesanan = \App\Models\Pesanan::with('details.menu', 'pembayarans', 'user')
                ->where('no_pesanan', $kodePesanan)->first();
            $type = 'dine_in';
        }

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        $viewTemplate = $type === 'dine_in' ? 'pesanan.invoice-dinein' : 'pesanan.invoice-pdf';

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView($viewTemplate, compact('pesanan', 'type', 'kodePesanan'))
            ->setPaper('a4', 'portrait');
        
        return $pdf->stream("E-Receipt-{$kodePesanan}.pdf");
    }
}
