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

        return redirect()->route('pesanan.status', $request->kode_pesanan)
            ->with('success', 'Bukti pembayaran berhasil dikirim! Kami akan memverifikasi dalam 1×24 jam.');
    }

    /** GET /pesan/status/{kodePesanan} */
    public function status($kodePesanan)
    {
        $pesanan = PesananCatering::with('buktiPembayarans', 'paket')
            ->where('kode_pesanan', $kodePesanan)->first();
        $type = 'catering';

        if (!$pesanan) {
            $pesanan = PesananNasiBox::with('buktiPembayarans', 'menu')
                ->where('kode_pesanan', $kodePesanan)->first();
            $type = 'nasi_box';
        }

        abort_unless($pesanan, 404, 'Pesanan tidak ditemukan.');

        return view('pesanan.status', compact('pesanan', 'type', 'kodePesanan'));
    }
}
