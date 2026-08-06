<?php

namespace App\Services;

use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\PilihanItemPaket;
use App\Models\ResepMenu;
use App\Models\StokBahan;
use Illuminate\Support\Collection;

/**
 * Layanan terpusat perhitungan kebutuhan bahan (FR-08) dan ketersediaan menu (FR-12).
 *
 * Kebutuhan bahan dihitung dari resep menu yang BENAR-BENAR dipilih konsumen:
 * - Menu satuan       : resep_menu × jumlah porsi.
 * - Paket (tetap)     : resep menu satuan yang ditautkan (menu_id_terkait) × jumlah.
 * - Paket (pilihan)   : resep pilihan yang dipilih konsumen (pilihan_item_paket.menu_id).
 *
 * Rumus: kebutuhan = takaran resep × jumlah porsi (FR-08).
 */
class KebutuhanBahanService
{
    /**
     * Hitung kebutuhan bahan untuk satu detail pesanan.
     *
     * @param  DetailPesanan  $detail  detail pesanan (menu + jumlah + pilihan yang dipilih)
     * @return Collection<int, array{bahan_baku_id:int, kebutuhan:float, satuan_id:int|null, menu_nama:string}>
     */
    public function kebutuhanBahanDetail(DetailPesanan $detail): Collection
    {
        $menu = $detail->menu;
        if (! $menu) {
            return collect();
        }

        $jumlahPorsi = (float) $detail->jumlah;

        return $this->kebutuhanMenu($menu, $jumlahPorsi, $detail);
    }

    /**
     * Apakah menu merupakan paket (punya item_paket)?
     */
    public function isPaket(Menu $menu): bool
    {
        return $menu->item_paket()->exists();
    }

    /**
     * Kebutuhan bahan untuk menu paket berdasarkan pilihan konsumen.
     * Pilihan yang TIDAK dipilih tidak ikut dihitung (FR-05 / Skenario 2).
     */
    protected function kebutuhanPaket(Menu $paket, float $jumlahPorsi, ?DetailPesanan $detail = null): Collection
    {
        $agregat = collect();

        foreach ($paket->item_paket()->with(['menu_terkait', 'opsi.menu'])->get() as $item) {
            if ($item->tipe_item === 'tetap' && $item->menu_id_terkait) {
                $agregat = $agregat->merge($this->agregasi(
                    $this->resepMenuIds($item->menu_terkait, $jumlahPorsi * (float) ($item->jumlah ?? 1)),
                    $item->menu_terkait->nama_menu
                ));
                continue;
            }

            // Kelompok pilihan: hanya menu yang dipilih konsumen
            $menuPilihanIds = $this->menuPilihanTerpilih($item, $detail);
            foreach ($menuPilihanIds as $menuId) {
                $menuPilihan = Menu::find($menuId);
                if (! $menuPilihan) {
                    continue;
                }
                $agregat = $agregat->merge($this->agregasi(
                    $this->resepMenuIds($menuPilihan, $jumlahPorsi),
                    $menuPilihan->nama_menu
                ));
            }
        }

        return $agregat->groupBy('bahan_baku_id')->map(function ($items, $bahanId) {
            return [
                'bahan_baku_id' => (int) $bahanId,
                'kebutuhan' => round($items->sum('kebutuhan'), 3),
                'satuan_id' => $items->first()['satuan_id'] ?? null,
                'menu_nama' => $items->pluck('menu_nama')->unique()->implode(', '),
            ];
        })->values();
    }

    /**
     * Menu satuan yang dipilih konsumen pada sebuah kelompok pilihan.
     */
    protected function menuPilihanTerpilih($item, ?DetailPesanan $detail): array
    {
        if ($detail && $detail->relationLoaded('pilihan_pesanan_catering') === false) {
            $detail->load('pilihan_pesanan_catering');
        }

        $terpilih = $detail
            ? $detail->pilihan_pesanan_catering->where('item_paket_id', $item->id)->pluck('pilihan_item_paket_id')->all()
            : [];

        // Fallback: jika belum ada pilihan tersimpan, gunakan pilihan pertama tiap kelompok.
        if (empty($terpilih)) {
            return $item->opsi()->whereNotNull('menu_id')->orderBy('urutan')->limit(1)->pluck('menu_id')->all();
        }

        return PilihanItemPaket::whereIn('id', $terpilih)
            ->whereNotNull('menu_id')
            ->pluck('menu_id')
            ->all();
    }

    /**
     * Ambil resep (bahan baku + kebutuhan) untuk satu menu dengan pengali jumlah.
     */
    protected function resepMenuIds(Menu $menu, float $pengali): Collection
    {
        return $menu->resep_menu()->get()->map(function (ResepMenu $resep) use ($pengali, $menu) {
            return [
                'bahan_baku_id' => (int) $resep->bahan_baku_id,
                'kebutuhan' => round((float) $resep->jumlah_kebutuhan * $pengali, 3),
                'satuan_id' => $resep->satuan_id,
                'menu_nama' => $menu->nama_menu,
            ];
        });
    }

    protected function agregasi(Collection $items, string $namaMenu): Collection
    {
        return $items->map(fn ($i) => $i + ['menu_nama' => $namaMenu]);
    }

    /**
     * Total kebutuhan bahan untuk seluruh detail sebuah pesanan.
     *
     * @return Collection<int, array{bahan_baku_id:int, kebutuhan:float}>
     */
    public function kebutuhanBahanPesanan($pesanan): Collection
    {
        $agregat = collect();

        foreach ($pesanan->detail_pesanan as $detail) {
            $agregat = $agregat->merge($this->kebutuhanBahanDetail($detail));
        }

        return $agregat->groupBy('bahan_baku_id')->map(function ($items, $bahanId) {
            return [
                'bahan_baku_id' => (int) $bahanId,
                'kebutuhan' => round($items->sum('kebutuhan'), 3),
            ];
        })->values();
    }

    /**
     * Jumlah maksimal porsi yang dapat dibuat dari stok saat ini (FR-12).
     * Diperhitungkan terhadap saldo pada jenis persediaan tertentu.
     *
     * @param  string  $jenisPersediaan  'harian' | 'catering'
     */
    public function porsiTersedia(Menu $menu, string $jenisPersediaan = 'harian'): float
    {
        if (! $menu->resep_menu()->exists()) {
            return PHP_FLOAT_MAX; // tanpa resep → dianggap selalu tersedia
        }

        $porsi = null;
        foreach ($menu->resep_menu()->get() as $resep) {
            $kebutuhanPerPorsi = (float) $resep->jumlah_kebutuhan;
            if ($kebutuhanPerPorsi <= 0) {
                continue;
            }
            $stok = (float) (StokBahan::where('bahan_baku_id', $resep->bahan_baku_id)
                ->where('jenis_persediaan', $jenisPersediaan)
                ->value('jumlah_stok') ?? 0);
            $maxPorsi = $stok / $kebutuhanPerPorsi;
            $porsi = $porsi === null ? $maxPorsi : min($porsi, $maxPorsi);
        }

        return (float) $porsi;
    }

    /**
     * Apakah bahan baku untuk N porsi menu cukup pada jenis persediaan? (FR-12)
     * Menu paket dihitung dari kebutuhan (resep komponen) yang ter-agregasi.
     *
     * @param  string  $jenisPersediaan  'harian' | 'catering'
     */
    public function bahanCukup(Menu $menu, float $jumlahPorsi, ?DetailPesanan $detail = null, string $jenisPersediaan = 'harian'): bool
    {
        if (! $this->isPaket($menu) && ! $menu->resep_menu()->exists()) {
            return true; // menu tanpa resep → tidak bisa divalidasi, dianggap cukup
        }

        foreach ($this->kebutuhanMenu($menu, $jumlahPorsi, $detail) as $item) {
            $stok = (float) (StokBahan::where('bahan_baku_id', $item['bahan_baku_id'])
                ->where('jenis_persediaan', $jenisPersediaan)
                ->value('jumlah_stok') ?? 0);
            if ($stok < $item['kebutuhan'] - 0.0001) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kebutuhan bahan ter-agregasi untuk satu menu + jumlah porsi.
     * Digunakan untuk cek ketersediaan sebelum pesanan dibuat.
     */
    public function kebutuhanMenu(Menu $menu, float $jumlahPorsi, ?DetailPesanan $detail = null): Collection
    {
        if (! $this->isPaket($menu)) {
            return $this->agregasi(
                $this->resepMenuIds($menu, $jumlahPorsi),
                $menu->nama_menu
            );
        }

        return $this->kebutuhanPaket($menu, $jumlahPorsi, $detail);
    }

    /**
     * Kurangi stok bahan baku berdasarkan pesanan (FR-12).
     * Jika stok tidak cukup, akan me-return false (proses harus dibatalkan).
     *
     * @param  Pesanan $pesanan
     * @param  string  $jenisPersediaan  'harian' | 'catering'
     * @return bool true jika sukses dipotong, false jika ada yang tidak cukup
     */
    public function deductBahanPesanan($pesanan, string $jenisPersediaan): bool
    {
        $kebutuhan = $this->kebutuhanBahanPesanan($pesanan);

        // 1. Cek dulu apakah semua stok mencukupi
        foreach ($kebutuhan as $item) {
            $stokTersedia = (float) (StokBahan::where('bahan_baku_id', $item['bahan_baku_id'])
                ->where('jenis_persediaan', $jenisPersediaan)
                ->value('jumlah_stok') ?? 0);
            
            if ($stokTersedia < $item['kebutuhan'] - 0.0001) {
                return false; // Ada stok yang tidak cukup
            }
        }

        // 2. Jika semua cukup, baru lakukan pemotongan
        foreach ($kebutuhan as $item) {
            $stokModel = StokBahan::where('bahan_baku_id', $item['bahan_baku_id'])
                ->where('jenis_persediaan', $jenisPersediaan)
                ->first();
                
            if ($stokModel) {
                $stokModel->jumlah_stok -= $item['kebutuhan'];
                $stokModel->save();
            }
        }

        return true;
    }
}
