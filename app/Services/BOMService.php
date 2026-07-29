<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\ResepMenu;
use App\Models\MutasiStok;
use Illuminate\Support\Facades\DB;
use Exception;

class BOMService
{
    /**
     * Mengecek ketersediaan bahan baku (BOM) untuk menu tertentu berdasarkan jumlah pesanan.
     *
     * @param int $menuId
     * @param int $jumlahPesan
     * @return bool
     */
    public static function cekKetersediaanBahan($menuId, $jumlahPesan = 1)
    {
        $menu = Menu::with('resep.bahanBaku')->find($menuId);
        if (!$menu) {
            return false;
        }

        // Cek status manual menu
        if ($menu->status === 'nonaktif' || $menu->status === 'habis') {
            return false;
        }

        // Cek seluruh bahan baku pada BOM/resep menu
        if ($menu->resep && $menu->resep->count() > 0) {
            foreach ($menu->resep as $resep) {
                $bahan = $resep->bahanBaku;
                if (!$bahan || $bahan->status == 0) {
                    return false;
                }

                $kebutuhanTotal = $resep->jumlah_kebutuhan * $jumlahPesan;
                
                // Validasi ketersediaan stok
                if ($bahan->stok < $kebutuhanTotal) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Mengurangi stok bahan baku secara otomatis berdasarkan BOM menu.
     * Pengurangan bersifat ATOMIK menggunakan DB Transaction & pessimistic locking.
     *
     * @param int $menuId
     * @param int $jumlahPesan
     * @param int|null $pesananId (opsional) ID referensi pesanan
     * @return bool
     * @throws Exception
     */
    public static function kurangiStokBahan($menuId, $jumlahPesan = 1, $pesananId = null)
    {
        // 1. Cek ketersediaan bahan (kini hanya cek apakah menu & bahan aktif)
        if (!self::cekKetersediaanBahan($menuId, $jumlahPesan)) {
            $menu = Menu::find($menuId);
            $namaMenu = $menu ? $menu->nama : "ID {$menuId}";
            throw new Exception("Gagal memproses pesanan: Menu '{$namaMenu}' tidak tersedia atau bahan baku utamanya dinonaktifkan.");
        }

        // 2. Eksekusi transaksi database secara ATOMIK
        return DB::transaction(function () use ($menuId, $jumlahPesan, $pesananId) {
            $menu = Menu::with('resep.bahanBaku')->findOrFail($menuId);

            foreach ($menu->resep as $resep) {
                // Lock record bahan baku untuk transaksi atomik pencegah race condition
                $bahanBaku = BahanBaku::where('id', $resep->bahan_baku_id)->lockForUpdate()->first();
                if (!$bahanBaku) {
                    throw new Exception("Bahan baku dengan ID {$resep->bahan_baku_id} tidak ditemukan.");
                }

                $kebutuhanTotal = $resep->jumlah_kebutuhan * $jumlahPesan;
                $stokAwal = (float) $bahanBaku->stok;

                if ($stokAwal < $kebutuhanTotal) {
                    throw new Exception("Stok {$bahanBaku->nama} tidak mencukupi. (Sisa: {$stokAwal}, Butuh: {$kebutuhanTotal})");
                }

                // Kurangi stok secara aman
                $bahanBaku->stok = $stokAwal - $kebutuhanTotal;
                $bahanBaku->save();

                // Rekam audit trail mutasi stok
                MutasiStok::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'user_id' => auth()->check() ? auth()->id() : 1,
                    'jenis_mutasi' => 'keluar',
                    'jumlah' => $kebutuhanTotal,
                    'sisa_stok' => $bahanBaku->stok,
                    'keterangan' => 'Penjualan menu: ' . $menu->nama . ' (Qty: ' . $jumlahPesan . ')',
                    'referensi' => $pesananId ? 'ORD-' . $pesananId : null,
                ]);
            }

            return true;
        });
    }
}
