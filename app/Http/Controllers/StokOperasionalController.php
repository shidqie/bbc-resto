<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use App\Models\StokBahan;
use Illuminate\Http\Request;

class StokOperasionalController extends Controller
{
    /**
     * Stok Bahan Baku Harian (Dine-In dan Nasi Box).
     */
    public function index(Request $request)
    {
        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan', 'stok_harian'])
            ->join('stok_bahan', function ($join) {
                $join->on('bahan_baku.id', '=', 'stok_bahan.bahan_baku_id')
                    ->where('stok_bahan.jenis_persediaan', StokBahan::JENIS_HARIAN);
            })
            ->select('bahan_baku.*', 'stok_bahan.jumlah_stok as stok');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_bahan', 'like', "%{$search}%");
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('bahan_baku.kategori_bahan_baku_id', $request->kategori);
        }

        if ($request->has('status') && $request->status != '') {
            if ($request->status == 'habis') {
                $query->whereRaw('stok_bahan.jumlah_stok <= 0');
            } elseif ($request->status == 'menipis') {
                $query->whereRaw('stok_bahan.jumlah_stok > 0 AND stok_bahan.jumlah_stok <= stok_bahan.stok_minimal');
            } elseif ($request->status == 'aman') {
                $query->whereRaw('stok_bahan.jumlah_stok > stok_bahan.stok_minimal');
            }
        }

        $query->orderByRaw('(stok_bahan.jumlah_stok / NULLIF(stok_bahan.stok_minimal, 0)) ASC');

        $bahanBakus = $query->paginate(15)->withQueryString();

        $kategoris = KategoriBahanBaku::all();

        $stats = [
            'total_bahan' => BahanBaku::count(),
            'total_aman' => StokBahan::harian()->whereColumn('jumlah_stok', '>', 'stok_minimal')->count(),
            'total_menipis' => StokBahan::harian()->where('jumlah_stok', '>', 0)
                ->whereColumn('jumlah_stok', '<=', 'stok_minimal')->count(),
            'total_habis' => StokBahan::harian()->where('jumlah_stok', '<=', 0)->count(),
        ];

        return view('inventory.stok-operasional.index', compact('bahanBakus', 'kategoris', 'stats'));
    }
}
