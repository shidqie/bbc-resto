<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\KategoriBahan;
use App\Models\Menu;
use App\Models\ResepMenu;
use App\Models\Satuan;
use Illuminate\Database\Seeder;

class DummyBOMSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan Satuan dasar tersedia
        $satuanKg = Satuan::where('singkatan', 'kg')->first() ?? Satuan::create(['nama_satuan' => 'Kilogram', 'singkatan' => 'kg']);
        $satuanL = Satuan::where('singkatan', 'L')->first() ?? Satuan::create(['nama_satuan' => 'Liter', 'singkatan' => 'L']);
        $satuanBtl = Satuan::where('singkatan', 'btl')->first() ?? Satuan::create(['nama_satuan' => 'Botol', 'singkatan' => 'btl']);
        $satuanPcs = Satuan::where('singkatan', 'pcs')->first() ?? Satuan::create(['nama_satuan' => 'Pcs', 'singkatan' => 'pcs']);

        // 2. Pastikan Kategori Bahan dasar tersedia
        $katPokok = KategoriBahan::firstOrCreate(['nama_kategori' => 'Bahan Pokok']);
        $katDaging = KategoriBahan::firstOrCreate(['nama_kategori' => 'Daging']);
        $katSayur = KategoriBahan::firstOrCreate(['nama_kategori' => 'Sayuran dan Bumbu']);
        $katMinuman = KategoriBahan::firstOrCreate(['nama_kategori' => 'Bahan Minuman']);

        // 3. Tambahkan Bahan Baku tambahan jika belum ada
        $bijiKopi = BahanBaku::firstOrCreate(
            ['nama_bahan' => 'Biji Kopi Arabika'],
            ['kode_bahan' => 'BB-011', 'kategori_bahan_id' => $katMinuman->id, 'satuan_id' => $satuanKg->id, 'stok' => 50, 'stok_minimum' => 10, 'harga_terakhir' => 120000, 'status' => true]
        );

        $susuUht = BahanBaku::firstOrCreate(
            ['nama_bahan' => 'Susu UHT / Fresh Milk'],
            ['kode_bahan' => 'BB-012', 'kategori_bahan_id' => $katMinuman->id, 'satuan_id' => $satuanL->id, 'stok' => 40, 'stok_minimum' => 10, 'harga_terakhir' => 18000, 'status' => true]
        );

        $gulaAren = BahanBaku::firstOrCreate(
            ['nama_bahan' => 'Sirup Gula Aren'],
            ['kode_bahan' => 'BB-013', 'kategori_bahan_id' => $katMinuman->id, 'satuan_id' => $satuanL->id, 'stok' => 20, 'stok_minimum' => 5, 'harga_terakhir' => 25000, 'status' => true]
        );

        $dagingBebek = BahanBaku::firstOrCreate(
            ['nama_bahan' => 'Daging Bebek Fresh'],
            ['kode_bahan' => 'BB-014', 'kategori_bahan_id' => $katDaging->id, 'satuan_id' => $satuanKg->id, 'stok' => 30, 'stok_minimum' => 5, 'harga_terakhir' => 65000, 'status' => true]
        );

        $tahuTempe = BahanBaku::firstOrCreate(
            ['nama_bahan' => 'Tahu & Tempe'],
            ['kode_bahan' => 'BB-015', 'kategori_bahan_id' => $katPokok->id, 'satuan_id' => $satuanPcs->id, 'stok' => 100, 'stok_minimum' => 20, 'harga_terakhir' => 2000, 'status' => true]
        );

        $sayurFresh = BahanBaku::firstOrCreate(
            ['nama_bahan' => 'Sayur & Lalapan Segar'],
            ['kode_bahan' => 'BB-016', 'kategori_bahan_id' => $katSayur->id, 'satuan_id' => $satuanKg->id, 'stok' => 25, 'stok_minimum' => 5, 'harga_terakhir' => 15000, 'status' => true]
        );

        // Ambil referensi bahan baku yang sudah ada
        $beras = BahanBaku::where('nama_bahan', 'LIKE', '%Beras%')->first();
        $ayam = BahanBaku::where('nama_bahan', 'LIKE', '%Ayam%')->first();
        $minyak = BahanBaku::where('nama_bahan', 'LIKE', '%Minyak%')->first();
        $cabai = BahanBaku::where('nama_bahan', 'LIKE', '%Cabai%')->first();
        $kecap = BahanBaku::where('nama_bahan', 'LIKE', '%Kecap%')->first();

        // 4. Proses pemberian BOM untuk SELURUH Menu
        $allMenus = Menu::all();

        foreach ($allMenus as $menu) {
            // Bersihkan resep lama jika ada
            ResepMenu::where('menu_id', $menu->id)->delete();

            $namaLower = strtolower($menu->nama);
            $ingredients = [];

            // A. Kopi & Minuman
            if (str_contains($namaLower, 'kopi') || str_contains($namaLower, 'americano') || str_contains($namaLower, 'cappuccino') || str_contains($namaLower, 'latte') || str_contains($namaLower, 'espresso') || str_contains($namaLower, 'macchiato') || str_contains($namaLower, 'matcha') || str_contains($namaLower, 'brew')) {
                $ingredients[] = ['bahan_baku_id' => $bijiKopi->id, 'qty' => 0.02, 'satuan' => 'kg', 'ket' => 'Takaran Biji Kopi (20g)'];
                if (str_contains($namaLower, 'susu') || str_contains($namaLower, 'cappuccino') || str_contains($namaLower, 'latte') || str_contains($namaLower, 'macchiato')) {
                    $ingredients[] = ['bahan_baku_id' => $susuUht->id, 'qty' => 0.15, 'satuan' => 'L', 'ket' => 'Fresh Milk (150ml)'];
                }
                if (str_contains($namaLower, 'gula aren') || str_contains($namaLower, 'caramel') || str_contains($namaLower, 'vanilla')) {
                    $ingredients[] = ['bahan_baku_id' => $gulaAren->id, 'qty' => 0.03, 'satuan' => 'L', 'ket' => 'Sirup Pemanis (30ml)'];
                }
            }
            // B. Bebek
            elseif (str_contains($namaLower, 'bebek')) {
                $ingredients[] = ['bahan_baku_id' => $dagingBebek->id, 'qty' => 0.25, 'satuan' => 'kg', 'ket' => '1 Potong Bebek (250g)'];
                if ($minyak) {
                    $ingredients[] = ['bahan_baku_id' => $minyak->id, 'qty' => 0.05, 'satuan' => 'L', 'ket' => 'Minyak Goreng'];
                }
                if (str_contains($namaLower, 'nasi') || str_contains($namaLower, 'liwet') || str_contains($namaLower, 'to')) {
                    if ($beras) {
                        $ingredients[] = ['bahan_baku_id' => $beras->id, 'qty' => 0.15, 'satuan' => 'kg', 'ket' => 'Beras Porsi'];
                    }
                }
                if (str_contains($namaLower, 'penyet') || str_contains($namaLower, 'bakar') || str_contains($namaLower, 'goreng')) {
                    if ($cabai) {
                        $ingredients[] = ['bahan_baku_id' => $cabai->id, 'qty' => 0.03, 'satuan' => 'kg', 'ket' => 'Cabai Bumbu/Sambal'];
                    }
                }
            }
            // C. Ayam / Ayam Kampung
            elseif (str_contains($namaLower, 'ayam')) {
                if ($ayam) {
                    $ingredients[] = ['bahan_baku_id' => $ayam->id, 'qty' => 0.20, 'satuan' => 'kg', 'ket' => '1 Potong Ayam (200g)'];
                }
                if ($minyak) {
                    $ingredients[] = ['bahan_baku_id' => $minyak->id, 'qty' => 0.05, 'satuan' => 'L', 'ket' => 'Minyak Goreng'];
                }
                if (str_contains($namaLower, 'nasi') || str_contains($namaLower, 'liwet') || str_contains($namaLower, 'to')) {
                    if ($beras) {
                        $ingredients[] = ['bahan_baku_id' => $beras->id, 'qty' => 0.15, 'satuan' => 'kg', 'ket' => 'Beras Porsi'];
                    }
                }
                if (str_contains($namaLower, 'penyet') || str_contains($namaLower, 'bakar')) {
                    if ($cabai) {
                        $ingredients[] = ['bahan_baku_id' => $cabai->id, 'qty' => 0.02, 'satuan' => 'kg', 'ket' => 'Cabai Bumbu'];
                    }
                    if ($kecap) {
                        $ingredients[] = ['bahan_baku_id' => $kecap->id, 'qty' => 0.02, 'satuan' => 'btl', 'ket' => 'Olesan Kecap'];
                    }
                }
            }
            // D. Tahu / Tempe
            elseif (str_contains($namaLower, 'tahu') || str_contains($namaLower, 'tempe')) {
                $ingredients[] = ['bahan_baku_id' => $tahuTempe->id, 'qty' => 2, 'satuan' => 'pcs', 'ket' => '2 Potong Tahu/Tempe'];
                if ($minyak) {
                    $ingredients[] = ['bahan_baku_id' => $minyak->id, 'qty' => 0.03, 'satuan' => 'L', 'ket' => 'Minyak Goreng'];
                }
            }
            // E. Sayur / Lalapan / Karedok / Jengkol / Pete
            elseif (str_contains($namaLower, 'sayur') || str_contains($namaLower, 'karedok') || str_contains($namaLower, 'lotek') || str_contains($namaLower, 'lalab') || str_contains($namaLower, 'jengkol') || str_contains($namaLower, 'pete') || str_contains($namaLower, 'pencok') || str_contains($namaLower, 'sepat') || str_contains($namaLower, 'peda')) {
                $ingredients[] = ['bahan_baku_id' => $sayurFresh->id, 'qty' => 0.15, 'satuan' => 'kg', 'ket' => 'Bahan Sayur Segar'];
                if ($cabai) {
                    $ingredients[] = ['bahan_baku_id' => $cabai->id, 'qty' => 0.02, 'satuan' => 'kg', 'ket' => 'Bumbu Sambal / Bumbu Kacang'];
                }
            }
            // F. Nasi / Liwet / Sambal Satuan
            elseif (str_contains($namaLower, 'nasi') || str_contains($namaLower, 'liwet')) {
                if ($beras) {
                    $ingredients[] = ['bahan_baku_id' => $beras->id, 'qty' => 0.18, 'satuan' => 'kg', 'ket' => 'Beras Porsi'];
                }
                if ($minyak) {
                    $ingredients[] = ['bahan_baku_id' => $minyak->id, 'qty' => 0.02, 'satuan' => 'L', 'ket' => 'Minyak Bumbu Liwet'];
                }
            }
            // G. Default Fallback untuk menu lainnya
            else {
                if ($ayam) {
                    $ingredients[] = ['bahan_baku_id' => $ayam->id, 'qty' => 0.15, 'satuan' => 'kg', 'ket' => 'Bahan Olahan Utama'];
                }
                if ($minyak) {
                    $ingredients[] = ['bahan_baku_id' => $minyak->id, 'qty' => 0.03, 'satuan' => 'L', 'ket' => 'Minyak Goreng'];
                }
            }

            // Simpan data ResepMenu / BOM
            foreach ($ingredients as $ing) {
                ResepMenu::create([
                    'menu_id' => $menu->id,
                    'bahan_baku_id' => $ing['bahan_baku_id'],
                    'jumlah_kebutuhan' => $ing['qty'],
                    'satuan' => $ing['satuan'],
                    'keterangan' => $ing['ket'] ?? null,
                ]);
            }
        }
    }
}
