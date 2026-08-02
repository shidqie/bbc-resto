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
                ->where('nomor_pesanan', $kodePesanan)
                ->orWhere('nomor_pesanan', 'like', "%{$kodePesanan}%")
                ->latest()
                ->first();

            if ($pesanan) {
                $jenisPesanan = match ($pesanan->jenis_pesanan_id) {
                    2 => 'Catering',
                    3 => 'Nasi Box',
                    default => 'Dine In / Takeaway',
                };
            }
        }

        return view('lacak.index', compact('pesanan', 'jenisPesanan', 'kodePesanan'));
    }
}
