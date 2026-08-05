<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $query = Pembayaran::with([
            'pesanan.jenis_pesanan', 
            'pesanan.pelanggan', 
            'pesanan.meja', 
            'pesanan.jadwal_pesanan',
            'metode_pembayaran', 
            'jenis_pembayaran', 
            'status_pembayaran',
            'diproses_oleh_pengguna'
        ])->orderBy('dibuat_pada', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhereHas('pesanan', function($qP) use ($search) {
                      $qP->where('nomor_pesanan', 'like', "%{$search}%");
                  });
            });
        }

        $pembayarans = $query->paginate(20)->withQueryString();

        return view('admin.pembayaran.index', compact('pembayarans'));
    }

    public function show($id)
    {
        $pembayaran = Pembayaran::with([
            'pesanan.jenis_pesanan',
            'pesanan.pelanggan',
            'pesanan.pembayaran.metode_pembayaran',
            'pesanan.pembayaran.status_pembayaran',
            'pesanan.pembayaran.jenis_pembayaran',
            'metode_pembayaran',
            'status_pembayaran',
            'jenis_pembayaran',
            'diproses_oleh_pengguna'
        ])->findOrFail($id);

        return view('admin.pembayaran.show', compact('pembayaran'));
    }
}
