<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use Illuminate\Http\Request;

class StokMenipisController extends Controller
{
    public function index(Request $request)
    {
        // Join ke stok_bahan_baku untuk bisa query stok
        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan', 'stok_bahan_baku'])
            ->join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
            ->select('bahan_baku.*', 'stok_bahan_baku.jumlah_stok as stok')
            ->whereRaw('stok_bahan_baku.jumlah_stok <= bahan_baku.stok_minimal');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_bahan', 'like', "%{$search}%");
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_bahan_baku_id', $request->kategori);
        }
        
        $query->orderByRaw('(stok_bahan_baku.jumlah_stok / NULLIF(bahan_baku.stok_minimal, 0)) ASC');

        $bahanBakus = $query->paginate(15)->withQueryString();
        
        $kategoris = KategoriBahanBaku::all();

        $stats = [
            'total_menipis' => BahanBaku::join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                ->whereRaw('stok_bahan_baku.jumlah_stok <= bahan_baku.stok_minimal AND stok_bahan_baku.jumlah_stok > 0')->count(),
            'total_habis' => BahanBaku::join('stok_bahan_baku', 'bahan_baku.id', '=', 'stok_bahan_baku.bahan_baku_id')
                ->where('stok_bahan_baku.jumlah_stok', '<=', 0)->count(),
        ];

        return view('inventory.stok.menipis', compact('bahanBakus', 'kategoris', 'stats'));
    }
}
