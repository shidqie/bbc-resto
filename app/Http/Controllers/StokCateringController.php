<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use App\Models\StokBahan;
use Illuminate\Http\Request;

class StokCateringController extends Controller
{
    /**
     * Stok Bahan Baku Catering (khusus pesanan Catering).
     */
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'stok');
        
        $kategoris = KategoriBahanBaku::all();

        if ($tab === 'riwayat') {
            $riwayatQuery = \App\Models\MutasiStok::with(['bahan_baku', 'bahan_baku.satuan'])
                ->where('jenis_persediaan', 'catering')
                ->where('jenis_mutasi_stok_id', 2) // Keluar
                ->orderBy('tanggal_mutasi', 'desc');

            if ($request->has('search') && $request->search != '') {
                $search = $request->search;
                $riwayatQuery->where(function($q) use ($search) {
                    $q->whereHas('bahan_baku', function($q) use ($search) {
                        $q->where('nama_bahan', 'like', "%{$search}%");
                    })->orWhere('referensi_id', 'like', "%{$search}%")
                      ->orWhere('catatan', 'like', "%{$search}%");
                });
            }

            if ($request->has('jenis_penggunaan') && $request->jenis_penggunaan != '') {
                if ($request->jenis_penggunaan == 'Catering') {
                    $riwayatQuery->where('catatan', 'like', '%Catering%');
                } elseif ($request->jenis_penggunaan == 'Penyesuaian') {
                    $riwayatQuery->whereNotNull('detail_penyesuaian_stok_id')
                                 ->orWhere('catatan', 'like', '%Penyesuaian%');
                }
            }

            $riwayats = $riwayatQuery->paginate(50)->withQueryString();
            
            return view('admin.persediaan.stok-catering.index', compact('tab', 'riwayats', 'kategoris'));
        }

        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan', 'stok_catering_balance'])
            ->join('stok_bahan', function ($join) {
                $join->on('bahan_baku.id', '=', 'stok_bahan.bahan_baku_id')
                    ->where('stok_bahan.jenis_persediaan', StokBahan::JENIS_CATERING);
            })
            ->select('bahan_baku.*', 'stok_bahan.jumlah_stok as stok', 'stok_bahan.stok_minimal as stok_min');

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

        $stats = [
            'total_bahan' => BahanBaku::count(),
            'total_aman' => StokBahan::catering()->whereColumn('jumlah_stok', '>', 'stok_minimal')->count(),
            'total_menipis' => StokBahan::catering()->where('jumlah_stok', '>', 0)
                ->whereColumn('jumlah_stok', '<=', 'stok_minimal')->count(),
            'total_habis' => StokBahan::catering()->where('jumlah_stok', '<=', 0)->count(),
        ];

        return view('admin.persediaan.stok-catering.index', compact('tab', 'bahanBakus', 'kategoris', 'stats'));
    }
}
