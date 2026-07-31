<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NormalisasiMinimalSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks to allow clean re-seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Kategori Menu
        DB::table('kategori_menu')->truncate();
        $kategoriMenu = [
            ['id' => 1, 'nama_kategori' => 'Nasi dan Paket', 'deskripsi' => 'Menu nasi dan paket utama'],
            ['id' => 2, 'nama_kategori' => 'Seafood', 'deskripsi' => 'Menu ikan dan hasil laut'],
            ['id' => 3, 'nama_kategori' => 'Steak dan Iga', 'deskripsi' => 'Menu steak dan iga'],
            ['id' => 4, 'nama_kategori' => 'Mie dan Bakso', 'deskripsi' => 'Menu mie dan bakso'],
            ['id' => 5, 'nama_kategori' => 'Minuman', 'deskripsi' => 'Minuman dingin dan hangat'],
            ['id' => 6, 'nama_kategori' => 'Catering', 'deskripsi' => 'Paket catering'],
            ['id' => 7, 'nama_kategori' => 'Nasi Box', 'deskripsi' => 'Paket nasi kotak'],
        ];
        foreach ($kategoriMenu as $k) {
            DB::table('kategori_menu')->updateOrInsert(['id' => $k['id']], $k);
        }

        // 2. Jenis Menu
        DB::table('jenis_menu')->truncate();
        $jenisMenu = [
            ['id' => 1, 'kode_jenis' => 'REGULER', 'nama_jenis' => 'Menu Reguler'],
            ['id' => 2, 'kode_jenis' => 'CATERING', 'nama_jenis' => 'Catering'],
            ['id' => 3, 'kode_jenis' => 'NASI_BOX', 'nama_jenis' => 'Nasi Box'],
        ];
        foreach ($jenisMenu as $jp) {
            DB::table('jenis_menu')->updateOrInsert(['id' => $jp['id']], $jp);
        }

        // 3. Satuan
        DB::table('satuan')->truncate();
        $satuan = [
            ['id' => 1, 'nama_satuan' => 'Kilogram', 'singkatan' => 'kg'],
            ['id' => 2, 'nama_satuan' => 'Gram', 'singkatan' => 'g'],
            ['id' => 3, 'nama_satuan' => 'Liter', 'singkatan' => 'l'],
            ['id' => 4, 'nama_satuan' => 'Mililiter', 'singkatan' => 'ml'],
            ['id' => 5, 'nama_satuan' => 'Buah', 'singkatan' => 'buah'],
            ['id' => 6, 'nama_satuan' => 'Sendok Makan', 'singkatan' => 'sdm'],
            ['id' => 7, 'nama_satuan' => 'Sendok Teh', 'singkatan' => 'sdt'],
        ];
        foreach ($satuan as $s) {
            DB::table('satuan')->updateOrInsert(['id' => $s['id']], $s);
        }

        // 4. Kategori Bahan Baku
        if (DB::getSchemaBuilder()->hasTable('kategori_bahan_baku')) {
            DB::table('kategori_bahan_baku')->truncate();
            $colName = DB::getSchemaBuilder()->hasColumn('kategori_bahan_baku', 'nama_kategori_bahan') ? 'nama_kategori_bahan' : 'nama_kategori';
            $katBahan = [
                ['id' => 1, $colName => 'Bahan Pokok'],
                ['id' => 2, $colName => 'Protein'],
                ['id' => 3, $colName => 'Bumbu'],
                ['id' => 4, $colName => 'Minuman'],
                ['id' => 5, $colName => 'Kemasan'],
            ];
            foreach ($katBahan as $kb) {
                DB::table('kategori_bahan_baku')->updateOrInsert(['id' => $kb['id']], $kb);
            }
        }

        // 5. Data Bahan Baku & Stok
        DB::table('bahan_baku')->truncate();
        $hasStokCol = DB::getSchemaBuilder()->hasColumn('bahan_baku', 'stok');

        $rawBahan = [
            ['id' => 1, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 2, 'kode_bahan' => 'BB001', 'nama_bahan' => 'Beras', 'stok_minimal' => 10000, 'stok' => 69700, 'harga_satuan' => 12.00], // 12/gram
            ['id' => 2, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB002', 'nama_bahan' => 'Ayam', 'stok_minimal' => 8000, 'stok' => 9760, 'harga_satuan' => 45.00],
            ['id' => 3, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB003', 'nama_bahan' => 'Ikan Gurame', 'stok_minimal' => 5000, 'stok' => 15000, 'harga_satuan' => 60.00],
            ['id' => 4, 'kategori_bahan_baku_id' => 2, 'satuan_id' => 2, 'kode_bahan' => 'BB004', 'nama_bahan' => 'Iga Sapi', 'stok_minimal' => 5000, 'stok' => 12000, 'harga_satuan' => 120.00],
            ['id' => 5, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB005', 'nama_bahan' => 'Bawang Merah', 'stok_minimal' => 2000, 'stok' => 8000, 'harga_satuan' => 30.00],
            ['id' => 6, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB006', 'nama_bahan' => 'Bawang Putih', 'stok_minimal' => 2000, 'stok' => 7000, 'harga_satuan' => 40.00],
            ['id' => 7, 'kategori_bahan_baku_id' => 3, 'satuan_id' => 2, 'kode_bahan' => 'BB007', 'nama_bahan' => 'Cabai', 'stok_minimal' => 2000, 'stok' => 1700, 'harga_satuan' => 60.00],
            ['id' => 8, 'kategori_bahan_baku_id' => 4, 'satuan_id' => 2, 'kode_bahan' => 'BB008', 'nama_bahan' => 'Teh', 'stok_minimal' => 500, 'stok' => 3000, 'harga_satuan' => 10.00],
            ['id' => 9, 'kategori_bahan_baku_id' => 1, 'satuan_id' => 2, 'kode_bahan' => 'BB009', 'nama_bahan' => 'Gula', 'stok_minimal' => 3000, 'stok' => 15000, 'harga_satuan' => 15.00],
            ['id' => 10, 'kategori_bahan_baku_id' => 5, 'satuan_id' => 5, 'kode_bahan' => 'BB010', 'nama_bahan' => 'Kotak Nasi', 'stok_minimal' => 50, 'stok' => 40, 'harga_satuan' => 2500.00],
        ];

        foreach ($rawBahan as $item) {
            $bb = [
                'id' => $item['id'],
                'kategori_bahan_baku_id' => $item['kategori_bahan_baku_id'],
                'satuan_id' => $item['satuan_id'],
                'kode_bahan' => $item['kode_bahan'],
                'nama_bahan' => $item['nama_bahan'],
                'stok_minimal' => $item['stok_minimal'],
                'harga_satuan' => $item['harga_satuan'],
            ];
            if ($hasStokCol) {
                $bb['stok'] = $item['stok'];
            }
            DB::table('bahan_baku')->updateOrInsert(['id' => $bb['id']], $bb);
        }

        // 5b. Stok Bahan Baku (Tabel Stok Terpisah)
        if (DB::getSchemaBuilder()->hasTable('stok_bahan_baku')) {
            DB::table('stok_bahan_baku')->truncate();
            $stokTimeCol = DB::getSchemaBuilder()->hasColumn('stok_bahan_baku', 'terakhir_diperbarui') ? 'terakhir_diperbarui' : 'diperbarui_pada';
            foreach ($rawBahan as $item) {
                $stokRecord = [
                    'bahan_baku_id' => $item['id'],
                    'jumlah_stok' => $item['stok'],
                ];
                if ($stokTimeCol) {
                    $stokRecord[$stokTimeCol] = now();
                }
                DB::table('stok_bahan_baku')->updateOrInsert(
                    ['bahan_baku_id' => $item['id']],
                    $stokRecord
                );
            }
        }

        // 6. Data Menu (Menu)
        DB::table('menu')->truncate();
        $menuList = [
            ['id' => 1, 'kategori_menu_id' => 1, 'jenis_menu_id' => 1, 'kode_menu' => 'MNU001', 'nama_menu' => 'Nasi Liwet Komplit', 'harga_jual' => 35000, 'deskripsi' => 'Nasi liwet dengan ayam dan lalapan', 'status_aktif' => true],
            ['id' => 2, 'kategori_menu_id' => 2, 'jenis_menu_id' => 1, 'kode_menu' => 'MNU002', 'nama_menu' => 'Gurame Bakar', 'harga_jual' => 75000, 'deskripsi' => 'Gurame bakar dengan sambal', 'status_aktif' => true],
            ['id' => 3, 'kategori_menu_id' => 3, 'jenis_menu_id' => 1, 'kode_menu' => 'MNU003', 'nama_menu' => 'Iga Bakar', 'harga_jual' => 65000, 'deskripsi' => 'Iga sapi bakar', 'status_aktif' => true],
            ['id' => 4, 'kategori_menu_id' => 4, 'jenis_menu_id' => 1, 'kode_menu' => 'MNU004', 'nama_menu' => 'Mie Goreng Spesial', 'harga_jual' => 28000, 'deskripsi' => 'Mie goreng dengan telur', 'status_aktif' => true],
            ['id' => 5, 'kategori_menu_id' => 5, 'jenis_menu_id' => 1, 'kode_menu' => 'MNU005', 'nama_menu' => 'Es Teh Manis', 'harga_jual' => 8000, 'deskripsi' => 'Teh manis dingin', 'status_aktif' => true],
            ['id' => 6, 'kategori_menu_id' => 6, 'jenis_menu_id' => 2, 'kode_menu' => 'CAT001', 'nama_menu' => 'Paket Catering A', 'harga_jual' => 50000, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true],
            ['id' => 7, 'kategori_menu_id' => 7, 'jenis_menu_id' => 3, 'kode_menu' => 'BOX001', 'nama_menu' => 'Nasi Box Hemat', 'harga_jual' => 17000, 'deskripsi' => 'Harga per kotak', 'status_aktif' => true],
        ];
        foreach ($menuList as $p) {
            DB::table('menu')->updateOrInsert(['id' => $p['id']], $p);
        }

        // 7. Resep Menu (resep_menu)
        if (DB::getSchemaBuilder()->hasTable('resep_menu')) {
            DB::table('resep_menu')->truncate();
            $jumlahCol = DB::getSchemaBuilder()->hasColumn('resep_menu', 'jumlah_kebutuhan') ? 'jumlah_kebutuhan' : 'jumlah';
            $hasSatuanCol = DB::getSchemaBuilder()->hasColumn('resep_menu', 'satuan_id');

            $resepList = [
                ['id' => 1, 'menu_id' => 1, 'bahan_baku_id' => 1, 'qty' => 150, 'satuan_id' => 2],
                ['id' => 2, 'menu_id' => 1, 'bahan_baku_id' => 2, 'qty' => 120, 'satuan_id' => 2],
                ['id' => 3, 'menu_id' => 1, 'bahan_baku_id' => 5, 'qty' => 10, 'satuan_id' => 2],
                ['id' => 4, 'menu_id' => 1, 'bahan_baku_id' => 6, 'qty' => 8, 'satuan_id' => 2],
                ['id' => 5, 'menu_id' => 2, 'bahan_baku_id' => 3, 'qty' => 500, 'satuan_id' => 2],
                ['id' => 6, 'menu_id' => 2, 'bahan_baku_id' => 7, 'qty' => 10, 'satuan_id' => 2],
                ['id' => 7, 'menu_id' => 3, 'bahan_baku_id' => 4, 'qty' => 250, 'satuan_id' => 2],
                ['id' => 8, 'menu_id' => 5, 'bahan_baku_id' => 8, 'qty' => 5, 'satuan_id' => 2],
                ['id' => 9, 'menu_id' => 5, 'bahan_baku_id' => 9, 'qty' => 20, 'satuan_id' => 2],
                ['id' => 10, 'menu_id' => 7, 'bahan_baku_id' => 1, 'qty' => 150, 'satuan_id' => 2],
                ['id' => 11, 'menu_id' => 7, 'bahan_baku_id' => 2, 'qty' => 100, 'satuan_id' => 2],
                ['id' => 12, 'menu_id' => 7, 'bahan_baku_id' => 10, 'qty' => 1, 'satuan_id' => 5],
            ];

            foreach ($resepList as $r) {
                $rec = [
                    'id' => $r['id'],
                    'menu_id' => $r['menu_id'],
                    'bahan_baku_id' => $r['bahan_baku_id'],
                    $jumlahCol => $r['qty'],
                ];
                if ($hasSatuanCol) {
                    $rec['satuan_id'] = $r['satuan_id'];
                }
                DB::table('resep_menu')->updateOrInsert(['id' => $r['id']], $rec);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
