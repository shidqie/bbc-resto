<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\MutasiStok;
use App\Models\StokBahan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Layanan terpusat untuk mutasi stok (FR-10, FR-11).
 * Seluruh perubahan saldo WAJIB lewat service ini agar selalu atomic,
 * menghasilkan kartu stok (stok_sebelum / stok_sesudah), dan memiliki referensi.
 *
 * Saldo dipisah berdasarkan jenis persediaan:
 *  - 'harian'   : Dine-In dan Nasi Box
 *  - 'catering' : Catering
 * Stok Harian dan Catering tidak pernah saling menukar secara otomatis.
 */
class StockService
{
    protected array $bahanBakuCache = [];

    /**
     * Tambah stok (masuk: penerimaan pengadaan, retur, pembalikan pembatalan).
     */
    public function addStock(
        int $bahanBakuId,
        float $jumlah,
        string $keterangan = 'Stok Masuk',
        ?int $jenisMutasiStokId = 1,
        ?int $userId = null,
        ?array $referensi = [],
        string $jenisPersediaan = StokBahan::JENIS_HARIAN,
    ): MutasiStok {
        return DB::transaction(function () use ($bahanBakuId, $jumlah, $keterangan, $jenisMutasiStokId, $userId, $referensi, $jenisPersediaan) {
            $stok = $this->lockStok($bahanBakuId, $jenisPersediaan);

            $stokSebelum = (float) $stok->jumlah_stok;
            $stok->jumlah_stok = $stokSebelum + $jumlah;
            $stok->terakhir_diperbarui = now();
            $stok->save();

            $bahan = BahanBaku::find($bahanBakuId);
            if ($bahan && \Illuminate\Support\Facades\Schema::hasColumn('bahan_baku', 'stok_harian')) {
                if ($jenisPersediaan === StokBahan::JENIS_HARIAN) {
                    $bahan->stok_harian = (float) $stok->jumlah_stok;
                    $bahan->save();
                }
            }

            return $this->catatMutasi($bahanBakuId, $jenisMutasiStokId, $jumlah, $stokSebelum, (float) $stok->jumlah_stok, $keterangan, $userId, $referensi, $jenisPersediaan);
        });
    }

    /**
     * Kurangi stok (keluar: pemakaian penjualan, bahan terbuang, retur keluar).
     * Akan melempar exception jika stok pada jenis persediaan tidak mencukupi.
     */
    public function deductStock(
        int $bahanBakuId,
        float $jumlah,
        string $keterangan = 'Stok Keluar',
        ?int $jenisMutasiStokId = 2,
        ?int $userId = null,
        ?array $referensi = [],
        bool $allowNegative = false,
        string $jenisPersediaan = StokBahan::JENIS_HARIAN,
    ): MutasiStok {
        return DB::transaction(function () use ($bahanBakuId, $jumlah, $keterangan, $jenisMutasiStokId, $userId, $referensi, $allowNegative, $jenisPersediaan) {
            $stok = $this->lockStok($bahanBakuId, $jenisPersediaan);

            $stokSebelum = (float) $stok->jumlah_stok;
            if (! $allowNegative && $stokSebelum < $jumlah) {
                $bahan = BahanBaku::find($bahanBakuId);
                throw new \RuntimeException(
                    'Stok '.($bahan->nama_bahan ?? 'bahan').' ('.($jenisPersediaan === 'catering' ? 'Catering' : 'Harian').') tidak mencukupi (sisa '.number_format($stokSebelum, 3).', dibutuhkan '.number_format($jumlah, 3).').'
                );
            }

            $stok->jumlah_stok = $stokSebelum - $jumlah;
            $stok->terakhir_diperbarui = now();
            $stok->save();

            return $this->catatMutasi($bahanBakuId, $jenisMutasiStokId, $jumlah, $stokSebelum, (float) $stok->jumlah_stok, $keterangan, $userId, $referensi, $jenisPersediaan);
        });
    }

    /**
     * Menyesuaikan stok (penyesuaian / opname fisik). Jenis mutasi harus id penyesuaian.
     */
    public function adjustStock(
        int $bahanBakuId,
        float $stokFisik,
        string $keterangan = 'Penyesuaian Stok',
        ?int $jenisMutasiStokId = null,
        ?int $userId = null,
        ?array $referensi = [],
        string $jenisPersediaan = StokBahan::JENIS_HARIAN,
    ): ?MutasiStok {
        return DB::transaction(function () use ($bahanBakuId, $stokFisik, $keterangan, $jenisMutasiStokId, $userId, $referensi, $jenisPersediaan) {
            $stok = $this->lockStok($bahanBakuId, $jenisPersediaan);

            $stokSebelum = (float) $stok->jumlah_stok;
            $selisih = $stokFisik - $stokSebelum;

            if (abs($selisih) < 0.0001) {
                return null;
            }

            $stok->jumlah_stok = $stokFisik;
            $stok->terakhir_diperbarui = now();
            $stok->save();

            // Penyesuaian dibuat sebagai jenis mutasi tersendiri (id 3 = penyesuaian, 4 = terbuang)
            $jenis = $jenisMutasiStokId ?? ($selisih > 0 ? 1 : 3);

            return $this->catatMutasi($bahanBakuId, $jenis, abs($selisih), $stokSebelum, $stokFisik, $keterangan.($selisih > 0 ? ' (Surplus)' : ' (Defisit)'), $userId, $referensi, $jenisPersediaan);
        });
    }

    /**
     * Kunci baris saldo stok berdasarkan bahan dan jenis persediaan
     * untuk mencegah race condition (FR-10, Skenario transaksi bersamaan).
     */
    protected function lockStok(int $bahanBakuId, string $jenisPersediaan): StokBahan
    {
        $stok = StokBahan::where('bahan_baku_id', $bahanBakuId)
            ->where('jenis_persediaan', $jenisPersediaan)
            ->first();
            
        if (!$stok) {
            $stok = StokBahan::create([
                'bahan_baku_id' => $bahanBakuId,
                'jenis_persediaan' => $jenisPersediaan,
                'jumlah_stok' => 0,
                'stok_minimal' => 0,
                'terakhir_diperbarui' => now()
            ]);
        }
        
        return StokBahan::where('id', $stok->id)->lockForUpdate()->first();
    }

    /**
     * Catat mutasi stok (kartu stok) dengan saldo sebelum/sesudah dan referensi.
     *
     * @param  array  $referensi  ['detail_pesanan_id' => ?, 'detail_penerimaan_bahan_id' => ?, 'detail_penyesuaian_stok_id' => ?]
     */
    protected function catatMutasi(
        int $bahanBakuId,
        ?int $jenisMutasiStokId,
        float $jumlah,
        float $stokSebelum,
        float $stokSesudah,
        string $keterangan,
        ?int $userId,
        array $referensi = [],
        string $jenisPersediaan = StokBahan::JENIS_HARIAN,
    ): MutasiStok {
        if (!isset($this->bahanBakuCache[$bahanBakuId])) {
            $this->bahanBakuCache[$bahanBakuId] = BahanBaku::findOrFail($bahanBakuId);
        }
        $bahanBaku = $this->bahanBakuCache[$bahanBakuId];

        return MutasiStok::create([
            'bahan_baku_id' => $bahanBakuId,
            'jenis_mutasi_stok_id' => $jenisMutasiStokId ?? 2,
            'jumlah' => $jumlah,
            'stok_sebelum' => $stokSebelum,
            'stok_sesudah' => $stokSesudah,
            'satuan_id' => $bahanBaku->satuan_id,
            'tanggal_mutasi' => now(),
            'jenis_persediaan' => $jenisPersediaan,
            'detail_pesanan_id' => $referensi['detail_pesanan_id'] ?? null,
            'detail_penerimaan_bahan_id' => $referensi['detail_penerimaan_bahan_id'] ?? null,
            'detail_penyesuaian_stok_id' => $referensi['detail_penyesuaian_stok_id'] ?? null,
            'dibuat_oleh' => $userId ?? Auth::id() ?? 1,
            'catatan' => $keterangan,
        ]);
    }
}
