<?php

namespace Database\Seeders;

use App\Models\BahanBaku;
use App\Models\KategoriBahanBaku;
use App\Models\Menu;
use App\Models\ResepMenu;
use App\Models\Satuan;
use App\Models\StokBahan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BahanBakuAndResepSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ResepMenu::truncate();
        StokBahan::truncate();
        BahanBaku::truncate();
        KategoriBahanBaku::truncate();
        Satuan::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Seed Satuan
        $satuans = [
            'kg' => Satuan::create(['nama_satuan' => 'Kilogram', 'singkatan' => 'kg']),
            'gram' => Satuan::create(['nama_satuan' => 'Gram', 'singkatan' => 'g']),
            'liter' => Satuan::create(['nama_satuan' => 'Liter', 'singkatan' => 'L']),
            'ml' => Satuan::create(['nama_satuan' => 'Mililiter', 'singkatan' => 'ml']),
            'pcs' => Satuan::create(['nama_satuan' => 'Pieces', 'singkatan' => 'pcs']),
            'porsi' => Satuan::create(['nama_satuan' => 'Porsi', 'singkatan' => 'prsi']),
            'ikat' => Satuan::create(['nama_satuan' => 'Ikat', 'singkatan' => 'ikt']),
        ];

        // 2. Seed Kategori Bahan Baku
        $kategoris = [
            'Sembako' => KategoriBahanBaku::create(['nama_kategori' => 'Sembako']),
            'Daging' => KategoriBahanBaku::create(['nama_kategori' => 'Daging & Seafood']),
            'Sayuran' => KategoriBahanBaku::create(['nama_kategori' => 'Sayur & Buah']),
            'Bumbu' => KategoriBahanBaku::create(['nama_kategori' => 'Bumbu & Rempah']),
            'Minuman' => KategoriBahanBaku::create(['nama_kategori' => 'Bahan Minuman']),
            'Lainnya' => KategoriBahanBaku::create(['nama_kategori' => 'Lainnya']),
        ];

        // 3. Seed Bahan Baku
        $bahanList = [
            ['nama' => 'Beras Putih', 'kategori' => 'Sembako', 'satuan' => 'kg', 'harga' => 15000],
            ['nama' => 'Ayam Potong', 'kategori' => 'Daging', 'satuan' => 'kg', 'harga' => 35000],
            ['nama' => 'Daging Sapi', 'kategori' => 'Daging', 'satuan' => 'kg', 'harga' => 120000],
            ['nama' => 'Ikan Lele', 'kategori' => 'Daging', 'satuan' => 'kg', 'harga' => 25000],
            ['nama' => 'Ikan Gurame', 'kategori' => 'Daging', 'satuan' => 'kg', 'harga' => 45000],
            ['nama' => 'Ikan Nila', 'kategori' => 'Daging', 'satuan' => 'kg', 'harga' => 30000],
            ['nama' => 'Telur Ayam', 'kategori' => 'Daging', 'satuan' => 'kg', 'harga' => 28000],
            ['nama' => 'Sayur Karedok', 'kategori' => 'Sayuran', 'satuan' => 'porsi', 'harga' => 5000],
            ['nama' => 'Selada', 'kategori' => 'Sayuran', 'satuan' => 'ikat', 'harga' => 3000],
            ['nama' => 'Timun', 'kategori' => 'Sayuran', 'satuan' => 'kg', 'harga' => 8000],
            ['nama' => 'Kemangi', 'kategori' => 'Sayuran', 'satuan' => 'ikat', 'harga' => 2000],
            ['nama' => 'Buah Melon', 'kategori' => 'Sayuran', 'satuan' => 'pcs', 'harga' => 15000],
            ['nama' => 'Buah Semangka', 'kategori' => 'Sayuran', 'satuan' => 'pcs', 'harga' => 20000],
            ['nama' => 'Jeruk', 'kategori' => 'Sayuran', 'satuan' => 'kg', 'harga' => 18000],
            ['nama' => 'Puding', 'kategori' => 'Lainnya', 'satuan' => 'pcs', 'harga' => 3000],
            ['nama' => 'Air Mineral Botol', 'kategori' => 'Minuman', 'satuan' => 'pcs', 'harga' => 3000],
            ['nama' => 'Teh Celup', 'kategori' => 'Minuman', 'satuan' => 'pcs', 'harga' => 500],
            ['nama' => 'Gula Pasir', 'kategori' => 'Sembako', 'satuan' => 'kg', 'harga' => 16000],
            ['nama' => 'Es Batu', 'kategori' => 'Minuman', 'satuan' => 'kg', 'harga' => 2000],
            ['nama' => 'Bumbu Sambal', 'kategori' => 'Bumbu', 'satuan' => 'porsi', 'harga' => 1000],
            ['nama' => 'Bumbu Ayam Bakar', 'kategori' => 'Bumbu', 'satuan' => 'porsi', 'harga' => 2000],
            ['nama' => 'Kerupuk', 'kategori' => 'Lainnya', 'satuan' => 'pcs', 'harga' => 500],
            ['nama' => 'Kentang', 'kategori' => 'Sayuran', 'satuan' => 'kg', 'harga' => 15000],
            ['nama' => 'Minyak Goreng', 'kategori' => 'Sembako', 'satuan' => 'liter', 'harga' => 18000],
            ['nama' => 'Mie Telor', 'kategori' => 'Sembako', 'satuan' => 'pcs', 'harga' => 5000],
            ['nama' => 'Sayur Sop', 'kategori' => 'Sayuran', 'satuan' => 'porsi', 'harga' => 3000],
        ];

        $bahanMap = [];
        foreach ($bahanList as $b) {
            $bahan = BahanBaku::create([
                'kode_bahan' => 'BHN-' . strtoupper(substr(md5($b['nama']), 0, 5)),
                'nama_bahan' => $b['nama'],
                'kategori_bahan_baku_id' => $kategoris[$b['kategori']]->id,
                'satuan_id' => $satuans[$b['satuan']]->id,
                'harga_satuan' => $b['harga'],
                'stok_minimal' => 10,
                'jenis_peruntukan' => 'Semua', // asumsi
                'status_aktif' => 1,
            ]);
            $bahanMap[strtolower($b['nama'])] = $bahan;

            // Buat Stok Harian & Catering
            StokBahan::create([
                'bahan_baku_id' => $bahan->id,
                'jenis_persediaan' => 'harian',
                'jumlah_stok' => 1000,
            ]);
            StokBahan::create([
                'bahan_baku_id' => $bahan->id,
                'jenis_persediaan' => 'catering',
                'jumlah_stok' => 1000,
            ]);
        }

        // 4. Buat Resep untuk setiap Menu Dine-In & Komponen Nasi Box/Catering
        $menus = Menu::all();
        foreach ($menus as $menu) {
            $name = strtolower($menu->nama_menu ?? $menu->nama);
            
            // Skip Paket menus since they use component recipes
            if ($menu->item_paket()->exists()) {
                continue;
            }

            $resep = [];

            if (str_contains($name, 'nasi')) {
                $resep[] = ['bahan' => 'beras putih', 'qty' => 0.1, 'satuan' => 'kg'];
            }
            if (str_contains($name, 'ayam')) {
                $resep[] = ['bahan' => 'ayam potong', 'qty' => 0.25, 'satuan' => 'kg'];
                if (str_contains($name, 'bakar')) {
                    $resep[] = ['bahan' => 'bumbu ayam bakar', 'qty' => 1, 'satuan' => 'porsi'];
                } else {
                    $resep[] = ['bahan' => 'minyak goreng', 'qty' => 0.05, 'satuan' => 'liter'];
                }
            }
            if (str_contains($name, 'lele')) {
                $resep[] = ['bahan' => 'ikan lele', 'qty' => 0.2, 'satuan' => 'kg'];
                $resep[] = ['bahan' => 'minyak goreng', 'qty' => 0.05, 'satuan' => 'liter'];
            }
            if (str_contains($name, 'nila')) {
                $resep[] = ['bahan' => 'ikan nila', 'qty' => 0.3, 'satuan' => 'kg'];
                $resep[] = ['bahan' => 'minyak goreng', 'qty' => 0.05, 'satuan' => 'liter'];
            }
            if (str_contains($name, 'gurame')) {
                $resep[] = ['bahan' => 'ikan gurame', 'qty' => 0.5, 'satuan' => 'kg'];
                $resep[] = ['bahan' => 'minyak goreng', 'qty' => 0.1, 'satuan' => 'liter'];
            }
            if (str_contains($name, 'mie')) {
                $resep[] = ['bahan' => 'mie telor', 'qty' => 1, 'satuan' => 'pcs'];
                $resep[] = ['bahan' => 'telur ayam', 'qty' => 0.06, 'satuan' => 'kg']; // 1 butir
            }
            if (str_contains($name, 'sop')) {
                $resep[] = ['bahan' => 'sayur sop', 'qty' => 1, 'satuan' => 'porsi'];
            }
            if (str_contains($name, 'karedok')) {
                $resep[] = ['bahan' => 'sayur karedok', 'qty' => 1, 'satuan' => 'porsi'];
            }
            if (str_contains($name, 'telur')) {
                $resep[] = ['bahan' => 'telur ayam', 'qty' => 0.06, 'satuan' => 'kg'];
            }
            if (str_contains($name, 'kentang')) {
                $resep[] = ['bahan' => 'kentang', 'qty' => 0.1, 'satuan' => 'kg'];
            }
            if (str_contains($name, 'melon')) {
                $resep[] = ['bahan' => 'buah melon', 'qty' => 0.05, 'satuan' => 'pcs'];
            }
            if (str_contains($name, 'semangka')) {
                $resep[] = ['bahan' => 'buah semangka', 'qty' => 0.05, 'satuan' => 'pcs'];
            }
            if (str_contains($name, 'jeruk')) {
                $resep[] = ['bahan' => 'jeruk', 'qty' => 0.1, 'satuan' => 'kg'];
            }
            if (str_contains($name, 'puding')) {
                $resep[] = ['bahan' => 'puding', 'qty' => 1, 'satuan' => 'pcs'];
            }
            if (str_contains($name, 'mineral')) {
                $resep[] = ['bahan' => 'air mineral botol', 'qty' => 1, 'satuan' => 'pcs'];
            }
            if (str_contains($name, 'teh')) {
                $resep[] = ['bahan' => 'teh celup', 'qty' => 1, 'satuan' => 'pcs'];
                $resep[] = ['bahan' => 'gula pasir', 'qty' => 0.02, 'satuan' => 'kg']; // 20g
                if (str_contains($name, 'es')) {
                    $resep[] = ['bahan' => 'es batu', 'qty' => 0.1, 'satuan' => 'kg'];
                }
            }

            // Default jika tidak match apa-apa
            if (empty($resep)) {
                if (str_contains($name, 'es')) {
                    $resep[] = ['bahan' => 'es batu', 'qty' => 0.1, 'satuan' => 'kg'];
                    $resep[] = ['bahan' => 'gula pasir', 'qty' => 0.02, 'satuan' => 'kg'];
                } else {
                    // Sembarang default untuk makanan
                    $resep[] = ['bahan' => 'beras putih', 'qty' => 0.1, 'satuan' => 'kg'];
                }
            }

            // Tambahkan bumbu sambal & lalapan untuk lauk utama
            if (str_contains($name, 'ayam') || str_contains($name, 'lele') || str_contains($name, 'nila') || str_contains($name, 'gurame')) {
                $resep[] = ['bahan' => 'bumbu sambal', 'qty' => 1, 'satuan' => 'porsi'];
                $resep[] = ['bahan' => 'timun', 'qty' => 0.02, 'satuan' => 'kg'];
                $resep[] = ['bahan' => 'kemangi', 'qty' => 0.1, 'satuan' => 'ikat'];
                $resep[] = ['bahan' => 'selada', 'qty' => 0.1, 'satuan' => 'ikat'];
            }

            // Insert resep
            foreach ($resep as $r) {
                if (isset($bahanMap[$r['bahan']])) {
                    $bahanDb = $bahanMap[$r['bahan']];
                    ResepMenu::create([
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $bahanDb->id,
                        'jumlah' => $r['qty'],
                        'satuan_id' => $satuans[$r['satuan']]->id,
                        'keterangan' => 'Otomatis dari seeder',
                    ]);
                }
            }
        }
    }
}
