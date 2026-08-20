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
                'pengiriman',
                'status_pesanan',
                'pembayaran',
            ])
            ->whereIn('jenis_pesanan_id', [2, 3]) // Catering & Nasi Box
            ->latest('dibuat_pada')
            ->get();

        return view('pelanggan.pesanan.index', compact('pelanggan', 'pesanans'));
    }

    public function show($id_pesanan): View
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        $pesanan = $pelanggan->pesanan()
            ->with([
                'detail_pesanan.menu',
                'jadwal_pesanan',
                'pengiriman',
                'status_pesanan',
                'pembayaran',
            ])
            ->where('id_pesanan', $id_pesanan)
            ->firstOrFail();

        $jenisPesanan = match ($pesanan->jenis_pesanan_id) {
            2 => 'Catering',
            3 => 'Nasi Box',
            default => 'Dine In',
        };

        return view('pelanggan.pesanan.show', compact('pelanggan', 'pesanan', 'jenisPesanan'));
    }
}
