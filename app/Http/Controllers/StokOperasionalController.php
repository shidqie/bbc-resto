<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use Illuminate\Http\Request;

class StokOperasionalController extends Controller
{
    public function index(Request $request)
    {
        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan', 'stok'])
            ->join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
            ->select('bahan_baku.*', 'stok_bahan_baku.jumlah_stok as stok');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_bahan', 'like', "%{$search}%");
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_bahan_baku_id', $request->kategori);
        }
        
        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'habis') {
                $query->whereRaw('stok_bahan_baku.jumlah_stok <= 0');
            } elseif ($request->status == 'menipis') {
                $query->whereRaw('stok_bahan_baku.jumlah_stok > 0 AND stok_bahan_baku.jumlah_stok <= bahan_baku.stok_minimal');
            } elseif ($request->status == 'aman') {
                $query->whereRaw('stok_bahan_baku.jumlah_stok > bahan_baku.stok_minimal');
            }
        }

        $query->orderByRaw('(stok_bahan_baku.jumlah_stok / NULLIF(bahan_baku.stok_minimal, 0)) ASC');

        $bahanBakus = $query->paginate(15)->withQueryString();
        
        $kategoris = KategoriBahanBaku::all();

        $stats = [
            'total_bahan' => BahanBaku::count(),
            'total_aman' => BahanBaku::join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                ->whereRaw('stok_bahan_baku.jumlah_stok > bahan_baku.stok_minimal')->count(),
            'total_menipis' => BahanBaku::join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                ->whereRaw('stok_bahan_baku.jumlah_stok <= bahan_baku.stok_minimal AND stok_bahan_baku.jumlah_stok > 0')->count(),
            'total_habis' => BahanBaku::join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                ->where('stok_bahan_baku.jumlah_stok', '<=', 0)->count(),
        ];

        return view('inventory.stok-operasional.index', compact('bahanBakus', 'kategoris', 'stats'));
    }
}
