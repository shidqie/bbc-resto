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
        $tab = $request->query('tab', 'stok');
        
        $kategoris = KategoriBahanBaku::all();

        if ($tab === 'riwayat') {
            $riwayatQuery = \App\Models\MutasiStok::with([
                'bahan_baku.satuan',
                'detail_pesanan.pesanan.pelanggan',
                'detail_pesanan.pesanan.jenis_pesanan',
                'detail_pesanan.pesanan.meja',
                'detail_penyesuaian_stok.penyesuaian_stok',
            ])
                ->where('jenis_persediaan', 'harian')
                ->where('jenis_mutasi_stok_id', 2) // Keluar
                ->orderBy('tanggal_mutasi', 'desc')
                ->orderBy('id', 'desc');

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
                // Filter by type: Dine-In, Nasi Box, Penyesuaian
                if ($request->jenis_penggunaan == 'Dine-In') {
                    $riwayatQuery->where(function($q) {
                        $q->where('catatan', 'like', '%Dine-In%')
                          ->orWhereHas('detail_pesanan.pesanan', function($sub) {
                              $sub->where('jenis_pesanan_id', 1);
                          });
                    });
                } elseif ($request->jenis_penggunaan == 'Nasi Box') {
                    $riwayatQuery->where(function($q) {
                        $q->where('catatan', 'like', '%Nasi Box%')
                          ->orWhereHas('detail_pesanan.pesanan', function($sub) {
                              $sub->where('jenis_pesanan_id', 3);
                          });
                    });
                } elseif ($request->jenis_penggunaan == 'Penyesuaian') {
                    $riwayatQuery->whereNotNull('detail_penyesuaian_stok_id')
                                 ->orWhere('catatan', 'like', '%Penyesuaian%');
                }
            }

            $riwayats = $riwayatQuery->paginate(100)->withQueryString();
            
            return view('admin.persediaan.stok-operasional.index', compact('tab', 'riwayats', 'kategoris'));
        }

        // Tab: Stok Saat Ini
        $query = BahanBaku::with(['kategori_bahan_baku', 'satuan', 'stok_harian'])
            ->join('stok_bahan', function ($join) {
                $join->on('bahan_baku.id', '=', 'stok_bahan.bahan_baku_id')
                    ->where('stok_bahan.jenis_persediaan', StokBahan::JENIS_HARIAN);
            })
            ->select('bahan_baku.*', 'stok_bahan.jumlah_stok as stok', 'stok_bahan.stok_minimal');

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

        $query->orderBy('stok_bahan.jumlah_stok', 'desc')
              ->orderBy('bahan_baku.nama_bahan', 'asc');

        $bahanBakus = $query->paginate(15)->withQueryString();

        $stats = [
            'total_bahan' => StokBahan::harian()->count(),
            'total_aman' => StokBahan::harian()->whereColumn('jumlah_stok', '>', 'stok_minimal')->count(),
            'total_menipis' => StokBahan::harian()->where('jumlah_stok', '>', 0)
                ->whereColumn('jumlah_stok', '<=', 'stok_minimal')->count(),
            'total_habis' => StokBahan::harian()->where('jumlah_stok', '<=', 0)->count(),
        ];

        return view('admin.persediaan.stok-operasional.index', compact('tab', 'bahanBakus', 'kategoris', 'stats'));
    }
}
