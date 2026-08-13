<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use Illuminate\Http\Request;

class LacakPesananController extends Controller
{
    public function index(Request $request)
    {
        $kodePesanan = $request->input('kode_pesanan');
        $pesanan = null;
        $jenisPesanan = null;

        if ($kodePesanan) {
            $query = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengiriman', 'pembayaran', 'pelanggan']);
            
            if (auth('pelanggan')->check()) {
                // If logged in, they can fuzzy search their own orders
                $query->where('pelanggan_id', auth('pelanggan')->id())
                      ->where(function($q) use ($kodePesanan) {
                          $q->where('id_pesanan', $kodePesanan)
                            ->orWhere('id_pesanan', 'like', "%{$kodePesanan}%");
                      });
            } else {
                // If guest, must be exact match and we don't check pelanggan_id
                $query->where('id_pesanan', $kodePesanan);
            }

            $pesanan = $query->latest()->first();

            if ($pesanan) {
                $jenisPesanan = match ($pesanan->jenis_pesanan_id) {
                    2 => 'Catering',
                    3 => 'Nasi Box',
                    default => 'Dine In',
                };
            }
        }

        return view('pelanggan.pesanan.lacak', compact('pesanan', 'jenisPesanan', 'kodePesanan'));
    }
}
