<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use App\Models\StokBahan;
use Illuminate\Http\Request;

class StokMenipisController extends Controller
{
    public function index(Request $request)
    {
        $jenisPersediaan = in_array($request->get('jenis_persediaan'), ['harian', 'catering'], true)
            ? $request->get('jenis_persediaan')
            : StokBahan::JENIS_HARIAN;

        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan'])
            ->join('stok_bahan', function ($join) use ($jenisPersediaan) {
                $join->on('bahan_baku.id', '=', 'stok_bahan.bahan_baku_id')
                    ->where('stok_bahan.jenis_persediaan', $jenisPersediaan);
            })
            ->select('bahan_baku.*', 'stok_bahan.jumlah_stok as stok', 'stok_bahan.stok_minimal as stok_min')
            ->whereColumn('stok_bahan.jumlah_stok', '<=', 'stok_bahan.stok_minimal')
            ->where('stok_bahan.stok_minimal', '>', 0);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_bahan', 'like', "%{$search}%");
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('bahan_baku.kategori_bahan_baku_id', $request->kategori);
        }

        $query->orderByRaw('(stok_bahan.jumlah_stok / NULLIF(stok_bahan.stok_minimal, 0)) ASC');

        $bahanBakus = $query->paginate(15)->withQueryString();

        $kategoris = KategoriBahanBaku::all();

        $stats = [
            'total_menipis' => StokBahan::where('jenis_persediaan', $jenisPersediaan)
                ->where('jumlah_stok', '>', 0)
                ->whereColumn('jumlah_stok', '<=', 'stok_minimal')->count(),
            'total_habis' => StokBahan::where('jenis_persediaan', $jenisPersediaan)
                ->where('jumlah_stok', '<=', 0)->count(),
        ];

        return view('admin.persediaan.stok.menipis', compact('bahanBakus', 'kategoris', 'stats', 'jenisPersediaan'));
    }
}
