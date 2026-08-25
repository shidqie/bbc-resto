<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;

class LacakPesananController extends Controller
{
    public function index(Request $request)
    {
        $kodePesanan = trim($request->input('kode_pesanan', ''));
        $pesanan = null;
        $jenisPesanan = null;
        $isDineInError = false;

        if ($kodePesanan) {
            // Cek apakah kode pesanan merupakan pesanan Dine-In (jenis_pesanan_id = 1)
            $checkDineIn = Pesanan::where('id_pesanan', $kodePesanan)
                ->where('jenis_pesanan_id', 1)
                ->exists();

            if ($checkDineIn) {
                $isDineInError = true;
            } else {
                // Hanya izinkan pelacakan khusus Katering (2) dan Nasi Box (3)
                $query = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengiriman', 'pembayaran', 'pelanggan', 'jenis_pesanan'])
                    ->whereIn('jenis_pesanan_id', [2, 3]);

                if (auth('pelanggan')->check()) {
                    // Jika login, pelanggan dapat mencari pesanan Nasi Box/Katering miliknya
                    $query->where('pelanggan_id', auth('pelanggan')->id())
                          ->where(function($q) use ($kodePesanan) {
                              $q->where('id_pesanan', $kodePesanan)
                                ->orWhere('id_pesanan', 'like', "%{$kodePesanan}%");
                          });
                } else {
                    // Jika tamu/guest, harus exact match kode pesanan
                    $query->where('id_pesanan', $kodePesanan);
                }

                $pesanan = $query->latest()->first();

                if ($pesanan) {
                    $jenisPesanan = match ($pesanan->jenis_pesanan_id) {
                        2 => 'Catering',
                        3 => 'Nasi Box',
                        default => optional($pesanan->jenis_pesanan)->nama_jenis ?? 'Pesanan',
                    };
                }
            }
        }

        return view('pelanggan.pesanan.lacak', compact('pesanan', 'jenisPesanan', 'kodePesanan', 'isDineInError'));
    }
}
