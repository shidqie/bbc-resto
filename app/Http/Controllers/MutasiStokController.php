<?php

namespace App\Http\Controllers;

use App\Models\MutasiStok;
use Illuminate\Http\Request;

class MutasiStokController extends Controller
{
    public function index(Request $request)
    {
        $query = MutasiStok::with(['bahan_baku.kategori_bahan_baku', 'dibuat_oleh_pengguna'])->orderBy('id', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('bahan_baku', function ($q) use ($search) {
                $q->where('nama_bahan', 'like', "%{$search}%");
            });
        }

        if ($request->has('jenis_mutasi_stok_id') && $request->jenis_mutasi_stok_id != '') {
            $query->where('jenis_mutasi_stok_id', $request->jenis_mutasi_stok_id);
        }

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('tanggal_mutasi', $request->tanggal);
        }

        if ($request->has('jenis_stok') && $request->jenis_stok != '') {
            $query->where('jenis_stok', $request->jenis_stok);
        }

        if ($request->has('referensi_id') && $request->referensi_id != '') {
            $query->where('referensi_id', 'like', "%{$request->referensi_id}%");
        }

        $mutasiStoks = $query->paginate(15)->withQueryString();

        $stats = [
            'total_transaksi' => MutasiStok::count(),
            'masuk_hari_ini' => MutasiStok::where('jenis_mutasi_stok_id', 1)->whereDate('tanggal_mutasi', today())->count(),
            'keluar_hari_ini' => MutasiStok::where('jenis_mutasi_stok_id', 2)->whereDate('tanggal_mutasi', today())->count(),
        ];

        return view('inventory.mutasi-stok.index', compact('mutasiStoks', 'stats'));
    }
}
