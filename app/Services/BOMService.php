<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\MutasiStok;
use App\Models\StokBahanBaku;
use Exception;
use Illuminate\Support\Facades\DB;

class BOMService
{
    /**
     * Mengecek ketersediaan bahan baku (BOM) untuk menu tertentu berdasarkan jumlah pesanan.
     */
    public static function cekKetersediaanBahan($menuId, $jumlahPesan = 1)
    {
        $menu = Menu::with('resep_menu.bahanBaku.stok_relasi')->find($menuId);
        if (! $menu) {
            return false;
        }

        if ($menu->status_aktif == 0 || $menu->status === 'nonaktif' || $menu->status === 'habis') {
            return false;
        }

        $resepList = $menu->resep_menu;

        if ($resepList && $resepList->count() > 0) {
            foreach ($resepList as $resep) {
                $bahan = $resep->bahanBaku;
                if (! $bahan || (isset($bahan->status_aktif) && ! $bahan->status_aktif)) {
                    return false;
                }

                $stokRecord = StokBahanBaku::where('bahan_baku_id', $resep->bahan_baku_id)->first();
                $stokAda = (float) ($stokRecord->jumlah_stok ?? 0);

                $kebutuhanTotal = $resep->jumlah_kebutuhan * $jumlahPesan;

                if ($stokAda < $kebutuhanTotal) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Mengurangi stok bahan baku secara otomatis berdasarkan BOM menu.
     * Pengurangan bersifat ATOMIK menggunakan DB Transaction & pessimistic locking.
     */
    public static function kurangiStokBahan($menuId, $jumlahPesan = 1, $pesananId = null)
    {
        if (! self::cekKetersediaanBahan($menuId, $jumlahPesan)) {
            $menu = Menu::find($menuId);
            $namaMenu = $menu ? ($menu->nama_produk ?? $menu->nama) : "ID {$menuId}";
            throw new Exception("Gagal memproses pesanan: Stok bahan baku untuk menu '{$namaMenu}' tidak mencukupi (Stok Kosong).");
        }

        return DB::transaction(function () use ($menuId, $jumlahPesan, $pesananId) {
            $menu = Menu::with('resep_menu.bahanBaku')->findOrFail($menuId);
            $resepList = $menu->resep_menu;

            if ($resepList) {
                foreach ($resepList as $resep) {
                    $bahanBaku = BahanBaku::where('id', $resep->bahan_baku_id)->first();
                    $stokRecord = StokBahanBaku::where('bahan_baku_id', $resep->bahan_baku_id)->lockForUpdate()->first();

                    if (! $stokRecord) {
                        $stokRecord = StokBahanBaku::create([
                            'bahan_baku_id' => $resep->bahan_baku_id,
                            'jumlah_stok' => 100,
                            'terakhir_diperbarui' => now(),
                        ]);
                    }

                    $kebutuhanTotal = $resep->jumlah_kebutuhan * $jumlahPesan;
                    $stokAwal = (float) $stokRecord->jumlah_stok;

                    if ($stokAwal < $kebutuhanTotal) {
                        $namaBahan = $bahanBaku->nama_bahan ?? $bahanBaku->nama ?? "ID {$resep->bahan_baku_id}";
                        throw new Exception("Stok {$namaBahan} tidak mencukupi. (Sisa: {$stokAwal}, Butuh: {$kebutuhanTotal})");
                    }

                    $sisaStok = $stokAwal - $kebutuhanTotal;
                    $stokRecord->where('bahan_baku_id', $resep->bahan_baku_id)->update([
                        'jumlah_stok' => $sisaStok,
                        'terakhir_diperbarui' => now(),
                    ]);

                    MutasiStok::create([
                        'bahan_baku_id' => $resep->bahan_baku_id,
                        'user_id' => auth()->check() ? auth()->id() : 1,
                        'jenis_mutasi' => 'keluar',
                        'jumlah' => $kebutuhanTotal,
                        'sisa_stok' => $sisaStok,
                        'keterangan' => 'Penjualan menu: '.($menu->nama_produk ?? $menu->nama).' (Qty: '.$jumlahPesan.')',
                        'referensi' => $pesananId ? 'ORD-'.$pesananId : null,
                    ]);
                }
            }

            return true;
        });
    }

    /**
     * Kembalikan stok bahan baku saat pesanan dibatalkan/void
     */
    public static function kembalikanStokBahan($menuId, $jumlahPesan = 1, $pesananId = null)
    {
        return DB::transaction(function () use ($menuId, $jumlahPesan, $pesananId) {
            $menu = Menu::with('resep_menu.bahanBaku')->find($menuId);
            if (! $menu) {
                return true;
            }

            $resepList = $menu->resep_menu;
            if ($resepList) {
                foreach ($resepList as $resep) {
                    $stokRecord = StokBahanBaku::where('bahan_baku_id', $resep->bahan_baku_id)->lockForUpdate()->first();
                    if ($stokRecord) {
                        $kebutuhanTotal = $resep->jumlah_kebutuhan * $jumlahPesan;
                        $sisaStok = (float) $stokRecord->jumlah_stok + $kebutuhanTotal;
                        $stokRecord->where('bahan_baku_id', $resep->bahan_baku_id)->update([
                            'jumlah_stok' => $sisaStok,
                            'terakhir_diperbarui' => now(),
                        ]);

                        MutasiStok::create([
                            'bahan_baku_id' => $resep->bahan_baku_id,
                            'user_id' => auth()->check() ? auth()->id() : 1,
                            'jenis_mutasi' => 'masuk',
                            'jumlah' => $kebutuhanTotal,
                            'sisa_stok' => $sisaStok,
                            'keterangan' => 'Void/Batal pesanan menu: '.($menu->nama_produk ?? $menu->nama).' (Qty: '.$jumlahPesan.')',
                            'referensi' => $pesananId ? 'VOID-'.$pesananId : null,
                        ]);
                    }
                }
            }

            return true;
        });
    }
}
