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
            $pesanan = Pesanan::with(['detail_pesanan.menu', 'jadwal_pesanan', 'pengantaran', 'pembayaran', 'pelanggan'])
                ->where('pelanggan_id', auth('pelanggan')->id())
                ->where(function($q) use ($kodePesanan) {
                    $q->where('id_pesanan', $kodePesanan)
                      ->orWhere('id_pesanan', 'like', "%{$kodePesanan}%");
                })
                ->latest()
                ->first();

            if ($pesanan) {
                $jenisPesanan = match ($pesanan->jenis_pesanan_id) {
                    2 => 'Catering',
                    3 => 'Nasi Box',
                    default => 'Dine In',
                };
            }
        }

        return view('lacak.index', compact('pesanan', 'jenisPesanan', 'kodePesanan'));
    }
}
