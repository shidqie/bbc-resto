<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;

class PesananController extends Controller
{
    public function index(Request $request)
    {
        $query = Pesanan::with(['jenis_pesanan', 'status_pesanan', 'meja', 'kasir', 'pembayaran.metode_pembayaran', 'jadwal_pesanan'])
            ->orderBy('dibuat_pada', 'desc');

        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis_pesanan_id', $request->jenis);
        }

        if ($request->has('status') && $request->status != '') {
            $query->where('status_pesanan_id', $request->status);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_pesanan', 'like', "%{$search}%")
                  ->orWhere('catatan', 'like', "%{$search}%");
            });
        }

        $pesanans = $query->paginate(20)->withQueryString();
        $jenis_pesanan = \App\Models\JenisPesanan::all();
        $status_pesanan = \App\Models\StatusPesanan::all();

        return view('admin.pesanan.index', compact('pesanans', 'jenis_pesanan', 'status_pesanan'));
    }

    public function show(Request $request, $id)
    {
        $pesanan = Pesanan::with([
            'jenis_pesanan', 'status_pesanan', 'meja', 'kasir', 'pelayan',
            'detail_pesanan.menu', 'pembayaran.metode_pembayaran', 'pembayaran.jenis_pembayaran',
            'tiket_dapur', 'jadwal_pesanan'
        ])->findOrFail($id);

        if ($request->ajax()) {
            return view('admin.pesanan.show_partial', compact('pesanan'));
        }

        return view('admin.pesanan.show', compact('pesanan'));
    }
}
