<?php

namespace App\Services;

use App\Models\BahanBaku;
use App\Models\DetailPesanan;
use App\Models\DetailPurchaseOrder;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\PilihanItemPaket;
use App\Models\PurchaseOrder;
use App\Models\ResepMenu;
use App\Models\StokBahan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Layanan terpusat perhitungan kebutuhan bahan (FR-08) dan ketersediaan menu (FR-12).
 *
 * Kebutuhan bahan dihitung dari resep menu yang BENAR-BENAR dipilih konsumen:
 * - Menu satuan       : resep_menu × jumlah porsi.
 * - Paket (tetap)     : resep menu satuan yang ditautkan (menu_id_terkait) × jumlah.
 * - Paket (pilihan)   : resep pilihan yang dipilih konsumen (pilihan_item_paket.menu_id).
 *
 * Rumus: kebutuhan = takaran resep per porsi × jumlah porsi (FR-08).
 */
class KebutuhanBahanService
{
    /**
     * Konversi satuan takaran resep ke satuan dasar bahan baku.
     */
    public static function convertUnit(float $qty, ?string $fromUnit, ?string $toUnit): float
    {
        $from = strtolower(trim((string)$fromUnit));
        $to = strtolower(trim((string)$toUnit));

        if ($from === $to || empty($from) || empty($to)) {
            return $qty;
        }

        // Gram to Kilogram
        if (in_array($from, ['gram', 'g', 'gr']) && in_array($to, ['kilogram', 'kg'])) {
            return $qty / 1000;
        }
        // Kilogram to Gram
        if (in_array($from, ['kilogram', 'kg']) && in_array($to, ['gram', 'g', 'gr'])) {
            return $qty * 1000;
        }
        // Mililiter to Liter
        if (in_array($from, ['mililiter', 'ml']) && in_array($to, ['liter', 'l'])) {
            return $qty / 1000;
        }
        // Liter to Mililiter
        if (in_array($from, ['liter', 'l']) && in_array($to, ['mililiter', 'ml'])) {
            return $qty * 1000;
        }

        return $qty;
    }

    /**
     * Cek apakah seluruh menu pada pesanan memiliki resep/BOM yang lengkap.
     *
     * @param  Pesanan  $pesanan
     * @return array{lengkap: bool, missing_menus: array<string>}
     */
    public function cekKelengkapanResepPesanan($pesanan): array
    {
        $missing = [];

        if (! $pesanan || ! $pesanan->detail_pesanan) {
            return ['lengkap' => true, 'missing_menus' => []];
        }

        foreach ($pesanan->detail_pesanan as $detail) {
            $menu = $detail->menu;
            if (! $menu) {
                continue;
            }

            if (! $this->isPaket($menu)) {
                if (! $menu->resep_menu()->exists()) {
                    $missing[] = $menu->nama_menu;
                }
            } else {
                foreach ($menu->item_paket()->with(['menu_terkait', 'opsi.menu'])->get() as $item) {
                    if ($item->tipe_item === 'wajib' && $item->menu_id_terkait) {
                        if ($item->menu_terkait && ! $item->menu_terkait->resep_menu()->exists()) {
                            $missing[] = $item->menu_terkait->nama_menu;
                        }
                    } elseif ($item->tipe_item === 'semua_didapat') {
                        foreach ($item->opsi as $opsi) {
                            if ($opsi->menu && ! $opsi->menu->resep_menu()->exists()) {
                                $missing[] = $opsi->menu->nama_menu;
                            }
                        }
                    } else {
                        // Kelompok pilihan
                        $menuPilihanIds = $this->menuPilihanTerpilih($item, $detail);
                        foreach ($menuPilihanIds as $menuId) {
                            $menuPilihan = Menu::find($menuId);
                            if ($menuPilihan && ! $menuPilihan->resep_menu()->exists()) {
                                $missing[] = $menuPilihan->nama_menu;
                            }
                        }
                    }
                }
            }
        }

        $uniqueMissing = array_values(array_unique($missing));

        return [
            'lengkap' => empty($uniqueMissing),
            'missing_menus' => $uniqueMissing,
        ];
    }

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

        // 1. Tambahkan resep yang terikat langsung pada menu paket (jika ada)
        if ($paket->resep_menu()->exists()) {
            $agregat = $agregat->merge($this->agregasi(
                $this->resepMenuIds($paket, $jumlahPorsi),
                $paket->nama_menu
            ));
        }

        // 2. Tambahkan resep dari komponen/item paket
        foreach ($paket->item_paket()->with(['menu_terkait', 'opsi.menu'])->get() as $item) {
            if ($item->tipe_item === 'wajib' && $item->menu_id_terkait) {
                $agregat = $agregat->merge($this->agregasi(
                    $this->resepMenuIds($item->menu_terkait, $jumlahPorsi * (float) ($item->jumlah ?? 1)),
                    $item->menu_terkait->nama_menu
                ));
                continue;
            }

            if ($item->tipe_item === 'semua_didapat') {
                foreach ($item->opsi as $opsi) {
                    if ($opsi->menu) {
                        $agregat = $agregat->merge($this->agregasi(
                            $this->resepMenuIds($opsi->menu, $jumlahPorsi * (float) ($item->jumlah ?? 1)),
                            $opsi->menu->nama_menu
                        ));
                    }
                }
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
                'menu_nama' => $items->pluck('menu_nama')->filter()->unique()->implode(', '),
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
        return $menu->resep_menu()->with(['bahan_baku.satuan', 'satuan'])->get()->map(function (ResepMenu $resep) use ($pengali, $menu) {
            $rawKebutuhan = (float) $resep->jumlah_kebutuhan * $pengali;
            
            // Konversi jika satuan resep dan satuan bahan baku berbeda
            $resepSatuan = optional($resep->satuan)->singkatan ?? optional($resep->satuan)->nama_satuan;
            $bahanSatuan = optional(optional($resep->bahan_baku)->satuan)->singkatan ?? optional(optional($resep->bahan_baku)->satuan)->nama_satuan;
            $convertedKebutuhan = self::convertUnit($rawKebutuhan, $resepSatuan, $bahanSatuan);

            return [
                'bahan_baku_id' => (int) $resep->bahan_baku_id,
                'kebutuhan' => round($convertedKebutuhan, 3),
                'satuan_id' => optional($resep->bahan_baku)->satuan_id ?? $resep->satuan_id,
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
     * @return Collection<int, array{bahan_baku_id:int, kebutuhan:float, menu_nama:string}>
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
                'menu_nama' => $items->pluck('menu_nama')->filter()->unique()->implode(', '),
            ];
        })->values();
    }

    /**
     * Hitung kebutuhan bahan baku, bandingkan dengan stok & PO aktif,
     * dan pisahkan secara akurat menjadi bahan yang kurang (wajib dipesan) dan bahan yang cukup.
     *
     * @param  Pesanan|Collection|array  $pesananInput
     * @param  string  $jenisPersediaan  'catering' | 'harian'
     * @return array{
     *    items_kurang: Collection,
     *    items_cukup: Collection,
     *    semua_kebutuhan: Collection,
     *    resep_lengkap: bool,
     *    missing_menus: array
     * }
     */
    public function hitungPengadaanPesanan($pesananInput, string $jenisPersediaan = 'catering'): array
    {
        $pesanans = is_iterable($pesananInput) && ! ($pesananInput instanceof Pesanan)
            ? collect($pesananInput)
            : collect([$pesananInput])->filter();

        $allMissingMenus = [];
        $agregat = collect();
        $pesananIds = [];

        foreach ($pesanans as $pesanan) {
            if (! $pesanan) {
                continue;
            }
            $pesananIds[] = $pesanan->id;
            $cekResep = $this->cekKelengkapanResepPesanan($pesanan);
            if (! $cekResep['lengkap']) {
                $allMissingMenus = array_merge($allMissingMenus, $cekResep['missing_menus']);
            }

            foreach ($pesanan->detail_pesanan as $detail) {
                $agregat = $agregat->merge($this->kebutuhanBahanDetail($detail));
            }
        }

        $allMissingMenus = array_values(array_unique($allMissingMenus));
        $resepLengkap = empty($allMissingMenus);

        if ($agregat->isEmpty()) {
            return [
                'items_kurang' => collect(),
                'items_cukup' => collect(),
                'semua_kebutuhan' => collect(),
                'resep_lengkap' => $resepLengkap,
                'missing_menus' => $allMissingMenus,
            ];
        }

        // Agregasikan kebutuhan per bahan_baku_id
        $groupedKebutuhan = $agregat->groupBy('bahan_baku_id')->map(function ($items, $bahanId) {
            return [
                'bahan_baku_id' => (int) $bahanId,
                'kebutuhan' => round($items->sum('kebutuhan'), 3),
                'menu_nama' => $items->pluck('menu_nama')->filter()->unique()->implode(', '),
            ];
        });

        $bahanIds = $groupedKebutuhan->keys();
        $bahans = BahanBaku::with('satuan')->whereIn('id', $bahanIds)->get()->keyBy('id');
        $stoks = StokBahan::where('jenis_persediaan', $jenisPersediaan)
            ->whereIn('bahan_baku_id', $bahanIds)
            ->get()->keyBy('bahan_baku_id');

        // Hitung PO yang sudah pernah dibuat untuk pesanan ini dan belum dibatalkan
        $poSebelumnya = DetailPurchaseOrder::whereHas('purchase_order', function ($q) use ($pesananIds) {
            $q->where('status', '!=', PurchaseOrder::DIBATALKAN);
            if (! empty($pesananIds)) {
                $q->whereHas('pengadaan_bahan', function ($pq) use ($pesananIds) {
                    $pq->whereIn('pesanan_id', $pesananIds);
                });
            }
        })->whereIn('bahan_baku_id', $bahanIds)
            ->select('bahan_baku_id', DB::raw('SUM(jumlah_dipesan) as total_pesan'))
            ->groupBy('bahan_baku_id')
            ->get()->keyBy('bahan_baku_id');

        $itemsKurang = collect();
        $itemsCukup = collect();
        $semuaKebutuhan = collect();

        foreach ($groupedKebutuhan as $row) {
            $bahan = $bahans->get($row['bahan_baku_id']);
            if (! $bahan) {
                continue;
            }

            $stok = $stoks->get($row['bahan_baku_id']);
            $totalKebutuhan = (float) $row['kebutuhan'];
            $stokSaatIni = $stok ? (float) $stok->jumlah_stok : 0;
            $sudahDipesan = isset($poSebelumnya[$row['bahan_baku_id']])
                ? (float) $poSebelumnya[$row['bahan_baku_id']]->total_pesan
                : 0;

            // Sisa kebutuhan yang belum di-cover oleh PO aktif
            $sisaKebutuhan = max(0, $totalKebutuhan - $sudahDipesan);

            // Kekurangan bahan aktual setelah dikurangi saldo stok tersedia (dalam base unit: Gram, Ml, Pcs)
            $kekurangan = max(0, $sisaKebutuhan - $stokSaatIni);

            // Satuan & Kuantitas Pembelian/Pengadaan (Kg, Liter, Pcs)
            $satuanBeli = \App\Helpers\UnitHelper::getPurchasingUnit($bahan->satuan);
            $satuanBeliId = \App\Helpers\UnitHelper::getPurchasingSatuanId($bahan->satuan);
            $jumlahBeli = \App\Helpers\UnitHelper::toPurchasingQuantity($kekurangan, $bahan->satuan);
            $hargaBeliSatuan = \App\Helpers\UnitHelper::toPurchasingPrice($bahan->harga_satuan ?? 0, $bahan->satuan);

            $itemData = (object) [
                'id' => $bahan->id,
                'id_bahan_baku' => $bahan->id_bahan_baku,
                'nama_bahan' => $bahan->nama_bahan,
                'satuan' => $bahan->satuan,
                'satuan_beli' => $satuanBeli,
                'satuan_beli_id' => $satuanBeliId,
                'satuan_dasar' => \App\Helpers\UnitHelper::getBaseUnit($bahan->satuan),
                'harga_satuan' => $hargaBeliSatuan,
                'kebutuhan_total' => $totalKebutuhan,
                'stok_saat_ini' => $stokSaatIni,
                'sudah_dipesan' => $sudahDipesan,
                'kekurangan' => $kekurangan,
                'jumlah_beli' => $jumlahBeli,
                'kebutuhan_bersih' => $jumlahBeli,
                'menu_nama' => $row['menu_nama'],
                'status_stok' => $kekurangan > 0 ? 'kurang' : 'cukup',
            ];

            $semuaKebutuhan->push($itemData);

            if ($kekurangan > 0) {
                $itemsKurang->push($itemData);
            } else {
                $itemsCukup->push($itemData);
            }
        }

        return [
            'items_kurang' => $itemsKurang,
            'items_cukup' => $itemsCukup,
            'semua_kebutuhan' => $semuaKebutuhan,
            'resep_lengkap' => $resepLengkap,
            'missing_menus' => $allMissingMenus,
        ];
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
     * Jumlah maksimal porsi yang dapat dibuat dari stok saat ini (FR-12).
     * Diperhitungkan terhadap saldo pada jenis persediaan tertentu.
     *
     * @param  string  $jenisPersediaan  'harian' | 'catering'
     */
    public function porsiTersedia(Menu $menu, string $jenisPersediaan = 'harian'): float
    {
        if (! $this->isPaket($menu) && ! $menu->resep_menu()->exists()) {
            return PHP_FLOAT_MAX; // tanpa resep → dianggap selalu tersedia
        }

        $kebutuhanList = $this->kebutuhanMenu($menu, 1);
        if ($kebutuhanList->isEmpty()) {
            return PHP_FLOAT_MAX;
        }

        $porsi = null;
        foreach ($kebutuhanList as $item) {
            $kebutuhanPerPorsi = (float) $item['kebutuhan'];
            if ($kebutuhanPerPorsi <= 0) {
                continue;
            }
            $stok = (float) (StokBahan::where('bahan_baku_id', $item['bahan_baku_id'])
                ->where('jenis_persediaan', $jenisPersediaan)
                ->value('jumlah_stok') ?? 0);
            $maxPorsi = $stok / $kebutuhanPerPorsi;
            $porsi = $porsi === null ? $maxPorsi : min($porsi, $maxPorsi);
        }

        return $porsi === null ? PHP_FLOAT_MAX : (float) $porsi;
    }

    /**
     * Apakah bahan baku untuk N porsi menu cukup pada jenis persediaan? (FR-12)
     * Menu paket dihitung dari kebutuhan (resep komponen) yang ter-agregasi.
     *
     * @param  string  $jenisPersediaan  'harian' | 'catering'
     */
    public function bahanCukup(Menu $menu, float $jumlahPorsi, ?DetailPesanan $detail = null, string $jenisPersediaan = 'harian'): bool
    {
        if (! $menu->status_aktif || $menu->status === 'nonaktif' || $menu->status === 'habis') {
            return false;
        }

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
     * Kurangi stok bahan baku berdasarkan pesanan (FR-12).
     * Jika stok tidak cukup, akan me-return false (proses harus dibatalkan).
     *
     * @param  Pesanan  $pesanan
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

        // 2. Jika semua cukup, baru lakukan pemotongan menggunakan StockService
        $stockService = app(\App\Services\StockService::class);
        foreach ($kebutuhan as $item) {
            $stockService->deductStock(
                $item['bahan_baku_id'],
                $item['kebutuhan'],
                'Pemakaian '.($pesanan->jenis_pesanan->nama_jenis_pesanan ?? 'Pesanan').' #'.$pesanan->id_pesanan,
                2, // Keluar
                auth()->id() ?? 1,
                ['detail_pesanan_id' => null],
                false, // allowNegative = false
                $jenisPersediaan
            );
        }

        return true;
    }
}
