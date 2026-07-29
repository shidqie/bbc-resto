<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LacakPesananController extends Controller
{
    public function index(Request $request)
    {
        $kodePesanan = $request->input('kode_pesanan');
        $pesanan = null;
        $jenisPesanan = null;

        if ($kodePesanan) {
            // Cek di Catering
            $pesanan = \App\Models\PesananCatering::where('kode_pesanan', $kodePesanan)
                ->orWhere('kontak', $kodePesanan)
                ->latest()
                ->first();
            
            if ($pesanan) {
                $jenisPesanan = 'Catering';
            } else {
                // Cek di Nasi Box
                $pesanan = \App\Models\PesananNasiBox::where('kode_pesanan', $kodePesanan)
                    ->orWhere('kontak', $kodePesanan)
                    ->latest()
                    ->first();
                
                if ($pesanan) {
                    $jenisPesanan = 'Nasi Box';
                } else {
                    // Cek di Dine In
                    $pesanan = \App\Models\PesananDinein::where('kode_pesanan', $kodePesanan)->first();
                    if ($pesanan) {
                        $jenisPesanan = 'Dine In / Takeaway';
                    }
                }
            }
        }

        return view('lacak.index', compact('pesanan', 'jenisPesanan', 'kodePesanan'));
    }
}
