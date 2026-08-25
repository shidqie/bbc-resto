<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\StokBahan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected $stockService;

    protected $kebutuhanBahanService;

    public function __construct(StockService $stockService, KebutuhanBahanService $kebutuhanBahanService)
    {
        $this->stockService = $stockService;
        $this->kebutuhanBahanService = $kebutuhanBahanService;
    }

    /**
     * Tentukan jenis persediaan dari sebuah pesanan.
     *  - Dine-In dan Nasi Box → harian
     *  - Catering             → catering
     *
     * @param  mixed  $pesanan  Pesanan|PesananDinein
     */
    public function jenisPersediaanPesanan($pesanan): string
    {
        if (isset($pesanan->jenis_pesanan_id)) {
            if ((int) $pesanan->jenis_pesanan_id === 2) {
                return StokBahan::JENIS_CATERING;
            }
            if (in_array((int) $pesanan->jenis_pesanan_id, [1, 3], true)) {
                return StokBahan::JENIS_HARIAN;
            }
        }

        if (isset($pesanan->jenis_pesanan) && $pesanan->jenis_pesanan) {
            $kode = strtoupper((string) ($pesanan->jenis_pesanan->kode_jenis ?? ''));
            $nama = strtolower((string) ($pesanan->jenis_pesanan->nama_jenis ?? ''));
            if (in_array($kode, ['KT', 'CAT', 'CATERING', 'KATERING'], true) || str_contains($nama, 'catering') || str_contains($nama, 'katering')) {
                return StokBahan::JENIS_CATERING;
            }
        }

        return StokBahan::JENIS_HARIAN;
    }

    /**
     * Selesaikan pesanan dan potong stok bahan baku sesuai resep menu (FR-08).
     *
     * Idempoten: detail yang sudah pernah dipotong (stock_deducted_at terisi)
     * dilewati, sehingga pemanggilan berulang tidak memotong stok dua kali.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function completeOrder(Pesanan $pesanan)
    {
        if ($pesanan->status_pesanan_id == 5 || $pesanan->status_pesanan_id == 6) {
            throw new \Exception('Pesanan sudah selesai atau dibatalkan.');
        }

        DB::transaction(function () use ($pesanan) {
            $this->potongStokPesanan($pesanan);
            $pesanan->status_pesanan_id = 5; // Selesai
            $pesanan->save();
        });
    }

    /**
     * Potong stok bahan untuk pesanan (FR-10).
     * Titik potong stok berbeda per jenis:
     *  - Dine-In:      saat pesanan dinyatakan selesai / pembayaran berhasil.
     *  - Catering/Nasi Box: saat produksi dimulai (status 2 = DIPROSES).
     *
     * Idempoten: detail yang sudah dipotong (stock_deducted_at terisi) dilewati.
     * Stok dipotong pada jenis persediaan sesuai jenis pesanan.
     * Status pesanan TIDAK diubah di sini.
     *
     * @param  \App\Models\Pesanan|\App\Models\PesananDinein  $pesanan
     * @return void
     */
    public function potongStokPesanan($pesanan)
    {
        DB::transaction(function () use ($pesanan) {
            $pesanan->load([
                'detail_pesanan.menu.resep_menu.bahan_baku',
                'detail_pesanan.pilihan_pesanan_catering',
                'jenis_pesanan',
            ]);

            $jenisPersediaan = $this->jenisPersediaanPesanan($pesanan);

            // Kunci semua detail yang terlibat agar tidak ada mutasi ganda (race).
            $detailIds = $pesanan->detail_pesanan->pluck('id')->all();
            $sudahDipotong = \App\Models\DetailPesanan::whereIn('id', $detailIds)
                ->whereNotNull('stock_deducted_at')
                ->pluck('id')
                ->all();

            foreach ($pesanan->detail_pesanan as $detail) {
                // Guard idempotent: skip detail yang stoknya sudah dipotong.
                if (in_array($detail->id, $sudahDipotong)) {
                    continue;
                }

                $menu = $detail->menu;
                if (! $menu) {
                    continue;
                }

                $kebutuhan = $this->kebutuhanBahanService->kebutuhanBahanDetail($detail);
                if ($kebutuhan->isEmpty()) {
                    continue;
                }

                $userId = Auth::id() ?? $pesanan->dibuat_oleh ?? \App\Models\Pengguna::value('id');
                $kodePesanan = $pesanan->id_pesanan ?? $pesanan->nomor_pesanan ?? $pesanan->id;

                foreach ($kebutuhan as $item) {
                    $this->stockService->deductStock(
                        $item['bahan_baku_id'],
                        $item['kebutuhan'],
                        "Pesanan #{$kodePesanan} (Menu: {$item['menu_nama']})",
                        2,
                        $userId,
                        ['detail_pesanan_id' => $detail->id],
                        false,
                        $jenisPersediaan,
                    );
                }

                $detail->stock_deducted_at = now();
                $detail->save();
            }
        });
    }

    /**
     * Kembalikan stok untuk detail pesanan yang sudah dipotong (Skenario F).
     * Idempoten: hanya detail dengan stock_deducted_at yang dikembalikan,
     * dan setelah dikembalikan penandanya dihapus agar tidak ganda.
     */
    public function restoreStockPesanan($pesanan): void
    {
        DB::transaction(function () use ($pesanan) {
            $pesanan->load([
                'detail_pesanan.menu.resep_menu.bahan_baku',
                'detail_pesanan.pilihan_pesanan_catering',
                'jenis_pesanan',
            ]);

            $jenisPersediaan = $this->jenisPersediaanPesanan($pesanan);
            $userId = Auth::id() ?? $pesanan->dibuat_oleh ?? \App\Models\Pengguna::value('id');
            $kodePesanan = $pesanan->id_pesanan ?? $pesanan->nomor_pesanan ?? $pesanan->id;

            foreach ($pesanan->detail_pesanan as $detail) {
                if (! $detail->stock_deducted_at) {
                    continue;
                }

                $kebutuhan = $this->kebutuhanBahanService->kebutuhanBahanDetail($detail);
                foreach ($kebutuhan as $item) {
                    $this->stockService->addStock(
                        $item['bahan_baku_id'],
                        $item['kebutuhan'],
                        "Pembatalan pesanan #{$kodePesanan} (Menu: {$item['menu_nama']}) - mutasi pembalik",
                        1,
                        $userId,
                        ['detail_pesanan_id' => $detail->id],
                        $jenisPersediaan,
                    );
                }

                $detail->stock_deducted_at = null;
                $detail->save();
            }
        });
    }

    /**
     * Batalkan pesanan.
     * Jika stok sudah dikurangi, buat mutasi pembalik dan kembalikan saldo
     * tanpa menghapus riwayat mutasi lama (Skenario F).
     *
     * @return void
     */
    public function cancelOrder(Pesanan $pesanan)
    {
        if ($pesanan->status_pesanan_id == 5) {
            throw new \Exception('Pesanan yang sudah selesai tidak bisa dibatalkan secara langsung.');
        }
        if ($pesanan->status_pesanan_id == 6) {
            throw new \Exception('Pesanan sudah dibatalkan.');
        }

        DB::transaction(function () use ($pesanan) {
            $sudahDipotong = \App\Models\DetailPesanan::where('pesanan_id', $pesanan->id)
                ->whereNotNull('stock_deducted_at')
                ->exists();

            if ($sudahDipotong) {
                $this->restoreStockPesanan($pesanan);
            }

            $pesanan->status_pesanan_id = 6; // Dibatalkan
            $pesanan->save();
        });
    }
}
