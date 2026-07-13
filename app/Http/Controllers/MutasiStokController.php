<?php

namespace App\Http\Controllers;

use App\Models\MutasiStok;
use App\Models\BahanBaku;
use Illuminate\Http\Request;

class MutasiStokController extends Controller
{
    public function index(Request $request)
    {
        $query = MutasiStok::with(['bahanBaku.kategoriBahan', 'user'])->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('bahanBaku', function($q) use ($search) {
                $q->where('nama_bahan', 'like', "%{$search}%");
            });
        }

        if ($request->has('jenis_mutasi') && $request->jenis_mutasi != '') {
            $query->where('jenis_mutasi', $request->jenis_mutasi);
        }

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('created_at', $request->tanggal);
        }

        $mutasiStoks = $query->paginate(15)->withQueryString();

        $stats = [
            'total_transaksi' => MutasiStok::count(),
            'masuk_hari_ini' => MutasiStok::where('jenis_mutasi', 'masuk')->whereDate('created_at', today())->count(),
            'keluar_hari_ini' => MutasiStok::where('jenis_mutasi', 'keluar')->whereDate('created_at', today())->count(),
        ];

        return view('bahan-baku.mutasi', compact('mutasiStoks', 'stats'));
    }
}
