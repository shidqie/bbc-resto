<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\StokBahan;
use Exception;
use Illuminate\Support\Facades\DB;

class BOMService
{
    /**
     * Mengecek ketersediaan bahan baku (BOM) untuk menu tertentu berdasarkan jumlah pesanan.
     * Dine-In memakai Stok Harian.
     */
    public static function cekKetersediaanBahan($menuId, $jumlahPesan = 1, $jenisPersediaan = StokBahan::JENIS_HARIAN)
    {
        $menu = Menu::find($menuId);
        if (! $menu) {
            return false;
        }

        $kebutuhanBahanService = app(KebutuhanBahanService::class);
        return $kebutuhanBahanService->bahanCukup($menu, (float) $jumlahPesan, null, $jenisPersediaan);
    }

    /**
     * Mengurangi stok bahan baku secara otomatis berdasarkan BOM menu.
     * Pengurangan bersifat ATOMIK menggunakan DB Transaction & pessimistic locking.
     */
    public static function kurangiStokBahan($menuId, $jumlahPesan = 1, $pesananId = null, $jenisPersediaan = StokBahan::JENIS_HARIAN)
    {
        if (! self::cekKetersediaanBahan($menuId, $jumlahPesan, $jenisPersediaan)) {
            $menu = Menu::find($menuId);
            $namaMenu = $menu ? ($menu->nama_produk ?? $menu->nama) : "ID {$menuId}";
            throw new Exception("Gagal memproses pesanan: Stok bahan baku untuk menu '{$namaMenu}' tidak mencukupi (Stok Kosong).");
        }

        return DB::transaction(function () use ($menuId, $jumlahPesan, $pesananId, $jenisPersediaan) {
            $menu = Menu::with('resep_menu.bahanBaku')->findOrFail($menuId);
            $resepList = $menu->resep_menu;
            $stockService = app(StockService::class);
            $userId = auth()->check() ? auth()->id() : 1;

            if ($resepList) {
                foreach ($resepList as $resep) {
                    $kebutuhanTotal = $resep->jumlah_kebutuhan * $jumlahPesan;

                    $stockService->deductStock(
                        $resep->bahan_baku_id,
                        (float) $kebutuhanTotal,
                        'Penjualan menu: '.($menu->nama_produk ?? $menu->nama).' (Qty: '.$jumlahPesan.')',
                        2,
                        $userId,
                        $pesananId ? ['pesanan_id' => $pesananId] : [],
                        false,
                        $jenisPersediaan,
                    );
                }
            }

            return true;
        });
    }

    /**
     * Kembalikan stok bahan baku saat pesanan dibatalkan/void.
     */
    public static function kembalikanStokBahan($menuId, $jumlahPesan = 1, $pesananId = null, $jenisPersediaan = StokBahan::JENIS_HARIAN)
    {
        return DB::transaction(function () use ($menuId, $jumlahPesan, $pesananId, $jenisPersediaan) {
            $menu = Menu::with('resep_menu.bahanBaku')->find($menuId);
            if (! $menu) {
                return true;
            }

            $resepList = $menu->resep_menu;
            $stockService = app(StockService::class);
            $userId = auth()->check() ? auth()->id() : 1;

            if ($resepList) {
                foreach ($resepList as $resep) {
                    $kebutuhanTotal = $resep->jumlah_kebutuhan * $jumlahPesan;

                    $stockService->addStock(
                        $resep->bahan_baku_id,
                        (float) $kebutuhanTotal,
                        'Void/Batal pesanan menu: '.($menu->nama_produk ?? $menu->nama).' (Qty: '.$jumlahPesan.')',
                        1,
                        $userId,
                        $pesananId ? ['pesanan_id' => $pesananId] : [],
                        $jenisPersediaan,
                    );
                }
            }

            return true;
        });
    }
}
