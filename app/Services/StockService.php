<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\MutasiStok;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockService
{
    /**
     * Menambah stok bahan baku.
     *
     * @param int $bahanBakuId ID Bahan Baku
     * @param float $jumlah Jumlah stok yang ditambahkan
     * @param string $keterangan Keterangan transaksi
     * @param int|null $userId ID User (default: auth user)
     * @return MutasiStok
     */
    public function addStock(int $bahanBakuId, float $jumlah, string $keterangan = 'Stok Masuk', ?int $userId = null, ?string $referensi = null)
    {
        return DB::transaction(function () use ($bahanBakuId, $jumlah, $keterangan, $userId, $referensi) {
            $bahanBaku = BahanBaku::lockForUpdate()->findOrFail($bahanBakuId);
            
            $bahanBaku->stok += $jumlah;
            $bahanBaku->save();

            return MutasiStok::create([
                'bahan_baku_id' => $bahanBakuId,
                'user_id' => $userId ?? Auth::id(),
                'jenis_mutasi' => 'masuk',
                'jumlah' => $jumlah,
                'sisa_stok' => $bahanBaku->stok,
                'keterangan' => $keterangan,
                'referensi' => $referensi,
            ]);
        });
    }

    /**
     * Mengurangi stok bahan baku.
     *
     * @param int $bahanBakuId ID Bahan Baku
     * @param float $jumlah Jumlah stok yang dikurangi
     * @param string $keterangan Keterangan transaksi
     * @param int|null $userId ID User (default: auth user)
     * @return MutasiStok
     */
    public function deductStock(int $bahanBakuId, float $jumlah, string $keterangan = 'Stok Keluar', ?int $userId = null, ?string $referensi = null)
    {
        return DB::transaction(function () use ($bahanBakuId, $jumlah, $keterangan, $userId, $referensi) {
            $bahanBaku = BahanBaku::lockForUpdate()->findOrFail($bahanBakuId);
            
            $bahanBaku->stok -= $jumlah;
            $bahanBaku->save();

            return MutasiStok::create([
                'bahan_baku_id' => $bahanBakuId,
                'user_id' => $userId ?? Auth::id(),
                'jenis_mutasi' => 'keluar',
                'jumlah' => $jumlah,
                'sisa_stok' => $bahanBaku->stok,
                'keterangan' => $keterangan,
                'referensi' => $referensi,
            ]);
        });
    }

    /**
     * Menyesuaikan stok bahan baku (Stock Opname).
     *
     * @param int $bahanBakuId ID Bahan Baku
     * @param float $stokFisik Jumlah stok fisik sebenarnya
     * @param string $keterangan Keterangan penyesuaian
     * @param int|null $userId ID User (default: auth user)
     * @return MutasiStok|null
     */
    public function adjustStock(int $bahanBakuId, float $stokFisik, string $keterangan = 'Penyesuaian Stok', ?int $userId = null)
    {
        return DB::transaction(function () use ($bahanBakuId, $stokFisik, $keterangan, $userId) {
            $bahanBaku = BahanBaku::lockForUpdate()->findOrFail($bahanBakuId);
            
            $selisih = $stokFisik - $bahanBaku->stok;
            
            if ($selisih == 0) {
                return null;
            }

            $bahanBaku->stok = $stokFisik;
            $bahanBaku->save();

            return MutasiStok::create([
                'bahan_baku_id' => $bahanBakuId,
                'user_id' => $userId ?? Auth::id(),
                'jenis_mutasi' => 'penyesuaian',
                'jumlah' => abs($selisih),
                'sisa_stok' => $bahanBaku->stok,
                'keterangan' => $keterangan . ($selisih > 0 ? ' (Surplus)' : ' (Defisit)'),
            ]);
        });
    }
}
