<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Pesanan;
use Illuminate\Http\Request;

class StokCateringController extends Controller
{
    public function index(Request $request)
    {
        $query = Pesanan::with(['stok_catering.bahan_baku.satuan'])
            ->whereHas('jenis_pesanan', function ($q) {
                $q->whereIn('kode_jenis', ['CAT', 'CATERING']);
            })
            ->orderBy('tanggal_pesanan', 'desc');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nomor_pesanan', 'like', "%{$search}%");
        }

        $pesanans = $query->paginate(10)->withQueryString();

        // Statistik bahan baku khusus peruntukan catering
        $stats = [
            'total_bahan' => BahanBaku::whereIn('jenis_peruntukan', ['Catering', 'Semua'])->count(),
            'total_aman' => BahanBaku::whereIn('jenis_peruntukan', ['Catering', 'Semua'])
                ->join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                ->whereRaw('stok_bahan_baku.jumlah_stok > bahan_baku.stok_minimal')->count(),
            'total_menipis' => BahanBaku::whereIn('jenis_peruntukan', ['Catering', 'Semua'])
                ->join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                ->whereRaw('stok_bahan_baku.jumlah_stok <= bahan_baku.stok_minimal AND stok_bahan_baku.jumlah_stok > 0')->count(),
            'total_habis' => BahanBaku::whereIn('jenis_peruntukan', ['Catering', 'Semua'])
                ->join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                ->where('stok_bahan_baku.jumlah_stok', '<=', 0)->count(),
        ];

        return view('inventory.stok-catering.index', compact('pesanans', 'stats'));
    }
}
