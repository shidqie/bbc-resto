<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahan;
use Illuminate\Http\Request;

class StokMenipisController extends Controller
{
    public function index(Request $request)
    {
        // Kondisi stok menipis: stok <= stok_minimum
        $query = BahanBaku::with(['kategoriBahan', 'satuan', 'supplier'])
                          ->whereRaw('stok <= stok_minimum');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_bahan', 'like', "%{$search}%");
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_bahan_id', $request->kategori);
        }
        
        // Urutkan berdasarkan persentase sisa stok (stok / stok_minimum) dari yang terkecil (paling mendesak)
        $query->orderByRaw('(stok / NULLIF(stok_minimum, 0)) ASC');

        $bahanBakus = $query->paginate(15)->withQueryString();
        
        $kategoris = KategoriBahan::all();

        $stats = [
            'total_menipis' => BahanBaku::whereRaw('stok <= stok_minimum AND stok > 0')->count(),
            'total_habis' => BahanBaku::where('stok', '<=', 0)->count(),
        ];

        return view('bahan-baku.menipis', compact('bahanBakus', 'kategoris', 'stats'));
    }
}
