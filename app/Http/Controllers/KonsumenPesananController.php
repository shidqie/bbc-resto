<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class KonsumenPesananController extends Controller
{
    public function index(): View
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        $pesanans = $pelanggan->pesanan()
            ->with([
                'detail_pesanan.menu',
                'jadwal_pesanan',
                'pengantaran',
                'status_pesanan',
                'pembayaran',
            ])
            ->whereIn('jenis_pesanan_id', [2, 3]) // Catering & Nasi Box
            ->latest()
            ->get();

        return view('akun.pesanan', compact('pelanggan', 'pesanans'));
    }
}
