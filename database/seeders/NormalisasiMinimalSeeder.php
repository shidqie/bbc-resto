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
        // 1-15: Kategori menu Dine In (bersumber dari docs/daftar_menu_dinein.md)
        // 16-17: Kategori untuk Catering dan Nasi Box
        DB::table('kategori_menu')->truncate();
        $kategoriMenu = [
            ['id' => 1, 'nama_kategori' => 'Paket Nasi Liwet (Porsi 5 Orang)', 'deskripsi' => 'Paket nasi liwet untuk porsi 5 orang'],
            ['id' => 2, 'nama_kategori' => 'Paket Nasi Ayam', 'deskripsi' => 'Paket nasi dengan ayam broiler'],
            ['id' => 3, 'nama_kategori' => 'Paket Nasi Ayam Kampung', 'deskripsi' => 'Paket nasi dengan ayam kampung'],
            ['id' => 4, 'nama_kategori' => 'Paket Nasi Bebek', 'deskripsi' => 'Paket nasi dengan bebek'],
            ['id' => 5, 'nama_kategori' => 'Sate', 'deskripsi' => 'Aneka sate'],
            ['id' => 6, 'nama_kategori' => 'Sop', 'deskripsi' => 'Aneka sop'],
            ['id' => 7, 'nama_kategori' => 'Gorengan', 'deskripsi' => 'Aneka gorengan'],
            ['id' => 8, 'nama_kategori' => 'Lauk Satuan', 'deskripsi' => 'Lauk yang dijual satuan'],
            ['id' => 9, 'nama_kategori' => 'Sayur dan Lalapan', 'deskripsi' => 'Aneka sayur dan lalapan'],
            ['id' => 10, 'nama_kategori' => 'Tambahan', 'deskripsi' => 'Nasi, sambal, dan pelengkap'],
            ['id' => 11, 'nama_kategori' => 'Cemilan', 'deskripsi' => 'Aneka cemilan'],
            ['id' => 12, 'nama_kategori' => 'Minuman Jus', 'deskripsi' => 'Aneka jus buah'],
            ['id' => 13, 'nama_kategori' => 'Minuman', 'deskripsi' => 'Minuman dingin dan hangat'],
            ['id' => 14, 'nama_kategori' => 'Minuman Coffee', 'deskripsi' => 'Minuman berbahan dasar kopi'],
            ['id' => 15, 'nama_kategori' => 'Minuman Non-Coffee', 'deskripsi' => 'Minuman non kopi'],
            ['id' => 16, 'nama_kategori' => 'Catering', 'deskripsi' => 'Paket catering'],
            ['id' => 17, 'nama_kategori' => 'Nasi Box', 'deskripsi' => 'Paket nasi kotak'],
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
        // Menu Dine In (jenis_menu_id = 1) bersumber dari docs/daftar_menu_dinein.md
        DB::table('menu')->truncate();

        $menuDineIn = [
            // Kategori 1: Paket Nasi Liwet (Porsi 5 Orang)
            ['kategori' => 1, 'nama' => 'Nasi Liwet, Ayam/Ikan Bakar atau Goreng, Tahu, Tempe, Kangkung/Karedok, Jengkol, Peda, Lalapan, Sambal', 'harga' => 170000],
            ['kategori' => 1, 'nama' => 'Nasi Liwet, Ayam Kampung Bakar atau Goreng, Tahu, Tempe, Kangkung/Karedok, Jengkol, Peda, Lalapan, Sambal', 'harga' => 205000],
            ['kategori' => 1, 'nama' => 'Nasi Liwet, Bebek Bakar atau Goreng, Tahu, Tempe, Kangkung/Karedok, Jengkol, Peda, Lalapan, Sambal', 'harga' => 255000],
            // Kategori 2: Paket Nasi Ayam
            ['kategori' => 2, 'nama' => 'Nasi Ayam Goreng', 'harga' => 26000],
            ['kategori' => 2, 'nama' => 'Nasi Ayam Bakar', 'harga' => 26000],
            ['kategori' => 2, 'nama' => 'Liwet Ayam Goreng', 'harga' => 27000],
            ['kategori' => 2, 'nama' => 'Liwet Ayam Bakar', 'harga' => 27000],
            ['kategori' => 2, 'nama' => 'Nasi Ayam Penyet Goreng', 'harga' => 27000],
            ['kategori' => 2, 'nama' => 'Nasi Ayam Penyet Bakar', 'harga' => 27000],
            ['kategori' => 2, 'nama' => 'Liwet Ayam Penyet Goreng', 'harga' => 28000],
            ['kategori' => 2, 'nama' => 'Liwet Ayam Penyet Bakar', 'harga' => 28000],
            ['kategori' => 2, 'nama' => 'Nasi Tutug Oncom Ayam Goreng', 'harga' => 27000],
            ['kategori' => 2, 'nama' => 'Nasi Tutug Oncom Ayam Bakar', 'harga' => 27000],
            // Kategori 3: Paket Nasi Ayam Kampung
            ['kategori' => 3, 'nama' => 'Nasi Ayam Kampung Goreng', 'harga' => 32000],
            ['kategori' => 3, 'nama' => 'Nasi Ayam Kampung Bakar', 'harga' => 32000],
            ['kategori' => 3, 'nama' => 'Liwet Ayam Kampung Goreng', 'harga' => 34000],
            ['kategori' => 3, 'nama' => 'Liwet Ayam Kampung Bakar', 'harga' => 34000],
            ['kategori' => 3, 'nama' => 'Nasi Ayam Kampung Penyet Goreng', 'harga' => 33000],
            ['kategori' => 3, 'nama' => 'Nasi Ayam Kampung Penyet Bakar', 'harga' => 33000],
            ['kategori' => 3, 'nama' => 'Liwet Ayam Kampung Penyet Goreng', 'harga' => 34000],
            ['kategori' => 3, 'nama' => 'Liwet Ayam Kampung Penyet Bakar', 'harga' => 34000],
            ['kategori' => 3, 'nama' => 'Nasi Tutug Oncom Ayam Kampung Goreng', 'harga' => 34000],
            ['kategori' => 3, 'nama' => 'Nasi Tutug Oncom Ayam Kampung Bakar', 'harga' => 34000],
            // Kategori 4: Paket Nasi Bebek
            ['kategori' => 4, 'nama' => 'Nasi Bebek Goreng', 'harga' => 60000],
            ['kategori' => 4, 'nama' => 'Nasi Bebek Bakar', 'harga' => 60000],
            ['kategori' => 4, 'nama' => 'Liwet Bebek Penyet Goreng', 'harga' => 61000],
            ['kategori' => 4, 'nama' => 'Liwet Bebek Penyet Bakar', 'harga' => 61000],
            ['kategori' => 4, 'nama' => 'Nasi Bebek Penyet Goreng', 'harga' => 61000],
            ['kategori' => 4, 'nama' => 'Nasi Bebek Penyet Bakar', 'harga' => 61000],
            ['kategori' => 4, 'nama' => 'Liwet Bebek Goreng', 'harga' => 63000],
            ['kategori' => 4, 'nama' => 'Liwet Bebek Bakar', 'harga' => 63000],
            ['kategori' => 4, 'nama' => 'Nasi Tutug Oncom Bebek Goreng', 'harga' => 63000],
            ['kategori' => 4, 'nama' => 'Nasi Tutug Oncom Bebek Bakar', 'harga' => 63000],
            // Kategori 5: Sate
            ['kategori' => 5, 'nama' => 'Sate Sapi', 'harga' => 40000],
            ['kategori' => 5, 'nama' => 'Sate Kambing', 'harga' => 40000],
            ['kategori' => 5, 'nama' => 'Sate Ayam', 'harga' => 28000],
            ['kategori' => 5, 'nama' => 'Sate Jando', 'harga' => 40000],
            // Kategori 6: Sop
            ['kategori' => 6, 'nama' => 'Sop Iga Sapi', 'harga' => 34000],
            ['kategori' => 6, 'nama' => 'Sop Iga Sapi + Nasi', 'harga' => 40000],
            // Kategori 7: Gorengan
            ['kategori' => 7, 'nama' => 'Kulit Goreng Jumbo', 'harga' => 20000],
            ['kategori' => 7, 'nama' => 'Kulit Goreng Jumbo + Nasi', 'harga' => 22000],
            // Kategori 8: Lauk Satuan
            ['kategori' => 8, 'nama' => 'Ayam Bakar', 'harga' => 23000],
            ['kategori' => 8, 'nama' => 'Ayam Kampung', 'harga' => 28000],
            ['kategori' => 8, 'nama' => 'Ayam Broiler', 'harga' => 18000],
            ['kategori' => 8, 'nama' => 'Bebek', 'harga' => 60000],
            ['kategori' => 8, 'nama' => 'Ikan', 'harga' => 14000],
            ['kategori' => 8, 'nama' => 'Tahu', 'harga' => 4000],
            ['kategori' => 8, 'nama' => 'Tempe', 'harga' => 4000],
            ['kategori' => 8, 'nama' => 'Peda', 'harga' => 13000],
            ['kategori' => 8, 'nama' => 'Sepat', 'harga' => 14000],
            // Kategori 9: Sayur dan Lalapan
            ['kategori' => 9, 'nama' => 'Jengkol', 'harga' => 13000],
            ['kategori' => 9, 'nama' => 'Pete', 'harga' => 13000],
            ['kategori' => 9, 'nama' => 'Kol Goreng', 'harga' => 13000],
            ['kategori' => 9, 'nama' => 'Jukut Goreng', 'harga' => 13000],
            ['kategori' => 9, 'nama' => 'Karedok', 'harga' => 15000],
            ['kategori' => 9, 'nama' => 'Lotek', 'harga' => 15000],
            ['kategori' => 9, 'nama' => 'Pencok Kacang', 'harga' => 15000],
            // Kategori 10: Tambahan
            ['kategori' => 10, 'nama' => 'Nasi Putih', 'harga' => 7000],
            ['kategori' => 10, 'nama' => 'Nasi Liwet', 'harga' => 9000],
            ['kategori' => 10, 'nama' => 'Nasi Tutug Oncom', 'harga' => 9000],
            ['kategori' => 10, 'nama' => 'Nasi Liwet Pulen', 'harga' => 14000],
            ['kategori' => 10, 'nama' => 'Nasi Tutug Oncom Pulen', 'harga' => 14000],
            ['kategori' => 10, 'nama' => 'Sambal', 'harga' => 6000],
            ['kategori' => 10, 'nama' => 'Lalapan + Sambal', 'harga' => 7000],
            // Kategori 11: Cemilan
            ['kategori' => 11, 'nama' => 'Tahu Gejrot', 'harga' => 13000],
            ['kategori' => 11, 'nama' => 'Tahu Sumedang', 'harga' => 13000],
            ['kategori' => 11, 'nama' => 'Cireng Rujak', 'harga' => 15000],
            ['kategori' => 11, 'nama' => 'Mendoan', 'harga' => 14000],
            // Kategori 12: Minuman Jus
            ['kategori' => 12, 'nama' => 'Jus Sirsak', 'harga' => 10000],
            ['kategori' => 12, 'nama' => 'Jus Mangga', 'harga' => 10000],
            ['kategori' => 12, 'nama' => 'Jus Jeruk', 'harga' => 10000],
            ['kategori' => 12, 'nama' => 'Jus Melon', 'harga' => 10000],
            ['kategori' => 12, 'nama' => 'Jus Jambu', 'harga' => 10000],
            ['kategori' => 12, 'nama' => 'Jus Stroberi', 'harga' => 12000],
            ['kategori' => 12, 'nama' => 'Jus Buah Naga', 'harga' => 12000],
            ['kategori' => 12, 'nama' => 'Jus Alpukat', 'harga' => 12000],
            // Kategori 13: Minuman
            ['kategori' => 13, 'nama' => 'Bandrek', 'harga' => 6000],
            ['kategori' => 13, 'nama' => 'Bajigur', 'harga' => 6000],
            ['kategori' => 13, 'nama' => 'Bandrek Susu', 'harga' => 7000],
            ['kategori' => 13, 'nama' => 'Bajigur Susu', 'harga' => 7000],
            ['kategori' => 13, 'nama' => 'Susu Putih', 'harga' => 7000],
            ['kategori' => 13, 'nama' => 'Susu Cokelat', 'harga' => 7000],
            ['kategori' => 13, 'nama' => 'Milo (Panas/Dingin)', 'harga' => 11000],
            ['kategori' => 13, 'nama' => 'Kopi Kapal Api', 'harga' => 10000],
            ['kategori' => 13, 'nama' => 'Kopi Good Day', 'harga' => 10000],
            ['kategori' => 13, 'nama' => 'Kopi Luwak', 'harga' => 10000],
            ['kategori' => 13, 'nama' => 'Kopi Indocafe', 'harga' => 10000],
            ['kategori' => 13, 'nama' => 'Kopi ABC Susu', 'harga' => 10000],
            ['kategori' => 13, 'nama' => 'Air Mineral', 'harga' => 5000],
            // Kategori 14: Minuman Coffee
            ['kategori' => 14, 'nama' => 'Es Kopi Susu', 'harga' => 20000],
            ['kategori' => 14, 'nama' => 'Es Kopi Susu Vanilla', 'harga' => 20000],
            ['kategori' => 14, 'nama' => 'Es Kopi Susu Gula Aren', 'harga' => 20000],
            ['kategori' => 14, 'nama' => 'Americano', 'harga' => 20000],
            ['kategori' => 14, 'nama' => 'Cappuccino', 'harga' => 20000],
            ['kategori' => 14, 'nama' => 'Café Latte', 'harga' => 20000],
            ['kategori' => 14, 'nama' => 'Espresso', 'harga' => 15000],
            ['kategori' => 14, 'nama' => 'Kopi Tubruk Arabika', 'harga' => 15000],
            ['kategori' => 14, 'nama' => 'Kopi Tubruk Robusta', 'harga' => 15000],
            // Kategori 15: Minuman Non-Coffee
            ['kategori' => 15, 'nama' => 'Caramel Macchiato', 'harga' => 20000],
            ['kategori' => 15, 'nama' => 'Hot Green Matcha', 'harga' => 20000],
        ];

        $menuList = [];
        foreach ($menuDineIn as $i => $m) {
            $id = $i + 1;
            $menuList[] = [
                'id' => $id,
                'kategori_menu_id' => $m['kategori'],
                'jenis_menu_id' => 1,
                'kode_menu' => 'MNU'.str_pad($id, 3, '0', STR_PAD_LEFT),
                'nama_menu' => $m['nama'],
                'harga_jual' => $m['harga'],
                'deskripsi' => null,
                'status_aktif' => true,
            ];
        }
        // Menu Catering dan Nasi Box
        $menuList[] = ['id' => 101, 'kategori_menu_id' => 16, 'jenis_menu_id' => 2, 'kode_menu' => 'CAT001', 'nama_menu' => 'Paket Catering A', 'harga_jual' => 47500, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true];
        $menuList[] = ['id' => 102, 'kategori_menu_id' => 16, 'jenis_menu_id' => 2, 'kode_menu' => 'CAT002', 'nama_menu' => 'Paket Catering B', 'harga_jual' => 42500, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true];
        $menuList[] = ['id' => 103, 'kategori_menu_id' => 17, 'jenis_menu_id' => 3, 'kode_menu' => 'BOX001', 'nama_menu' => 'Nasi Box Hemat', 'harga_jual' => 17000, 'deskripsi' => 'Harga per kotak', 'status_aktif' => true];
        foreach ($menuList as $p) {
            DB::table('menu')->updateOrInsert(['id' => $p['id']], $p);
        }

        // 7. Resep Menu (resep_menu)
        if (DB::getSchemaBuilder()->hasTable('resep_menu')) {
            DB::table('resep_menu')->truncate();
            $jumlahCol = DB::getSchemaBuilder()->hasColumn('resep_menu', 'jumlah_kebutuhan') ? 'jumlah_kebutuhan' : 'jumlah';
            $hasSatuanCol = DB::getSchemaBuilder()->hasColumn('resep_menu', 'satuan_id');

            $resepList = [
                ['id' => 1, 'menu_id' => 101, 'bahan_baku_id' => 1, 'qty' => 150, 'satuan_id' => 2],
                ['id' => 2, 'menu_id' => 101, 'bahan_baku_id' => 2, 'qty' => 100, 'satuan_id' => 2],
                ['id' => 3, 'menu_id' => 101, 'bahan_baku_id' => 10, 'qty' => 1, 'satuan_id' => 5],
                ['id' => 4, 'menu_id' => 103, 'bahan_baku_id' => 1, 'qty' => 150, 'satuan_id' => 2],
                ['id' => 5, 'menu_id' => 103, 'bahan_baku_id' => 2, 'qty' => 100, 'satuan_id' => 2],
                ['id' => 6, 'menu_id' => 103, 'bahan_baku_id' => 10, 'qty' => 1, 'satuan_id' => 5],
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

        // 8. Komponen Paket Catering (komponen_paket & pilihan_komponen_paket)
        if (DB::getSchemaBuilder()->hasTable('komponen_paket')) {
            DB::table('pilihan_komponen_paket')->truncate();
            DB::table('komponen_paket')->truncate();

            $komponenList = [
                // --- Paket Catering A (menu_id 101) ---
                ['id' => 1, 'menu_id' => 101, 'nama_komponen' => 'Nasi Putih', 'tipe_komponen' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 1],
                ['id' => 2, 'menu_id' => 101, 'nama_komponen' => 'Aneka Sup', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
                ['id' => 3, 'menu_id' => 101, 'nama_komponen' => 'Aneka Olahan Daging Sapi', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 3],
                ['id' => 4, 'menu_id' => 101, 'nama_komponen' => 'Aneka Olahan Tambahan', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 4],
                ['id' => 5, 'menu_id' => 101, 'nama_komponen' => 'Sayuran', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 5],
                ['id' => 6, 'menu_id' => 101, 'nama_komponen' => 'Kerupuk Udang', 'tipe_komponen' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 6],
                ['id' => 7, 'menu_id' => 101, 'nama_komponen' => 'Air Mineral', 'tipe_komponen' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 7],
                ['id' => 8, 'menu_id' => 101, 'nama_komponen' => 'Stall', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 8],
                ['id' => 9, 'menu_id' => 101, 'nama_komponen' => 'Dessert', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 9],
                // --- Paket Catering B (menu_id 102) ---
                ['id' => 10, 'menu_id' => 102, 'nama_komponen' => 'Nasi Putih', 'tipe_komponen' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 1],
                ['id' => 11, 'menu_id' => 102, 'nama_komponen' => 'Aneka Sup', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
                ['id' => 12, 'menu_id' => 102, 'nama_komponen' => 'Aneka Olahan Ayam', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 3],
                ['id' => 13, 'menu_id' => 102, 'nama_komponen' => 'Aneka Olahan Tambahan', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 4],
                ['id' => 14, 'menu_id' => 102, 'nama_komponen' => 'Sayuran', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 5],
                ['id' => 15, 'menu_id' => 102, 'nama_komponen' => 'Kerupuk Udang', 'tipe_komponen' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 6],
                ['id' => 16, 'menu_id' => 102, 'nama_komponen' => 'Air Mineral', 'tipe_komponen' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 7],
                ['id' => 17, 'menu_id' => 102, 'nama_komponen' => 'Stall', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 8],
                ['id' => 18, 'menu_id' => 102, 'nama_komponen' => 'Dessert', 'tipe_komponen' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 9],
            ];
            foreach ($komponenList as $k) {
                DB::table('komponen_paket')->updateOrInsert(['id' => $k['id']], $k);
            }

            $pilihanList = [
                // Paket A — Aneka Sup (komponen 2)
                ['id' => 1, 'komponen_paket_id' => 2, 'nama_pilihan' => 'Sup Kimlo', 'urutan' => 1],
                ['id' => 2, 'komponen_paket_id' => 2, 'nama_pilihan' => 'Sup Bakso', 'urutan' => 2],
                ['id' => 3, 'komponen_paket_id' => 2, 'nama_pilihan' => 'Sup Ayam Sosis', 'urutan' => 3],
                // Paket A — Daging Sapi (komponen 3)
                ['id' => 4, 'komponen_paket_id' => 3, 'nama_pilihan' => 'Sapi Teriyaki', 'urutan' => 1],
                ['id' => 5, 'komponen_paket_id' => 3, 'nama_pilihan' => 'Rendang', 'urutan' => 2],
                ['id' => 6, 'komponen_paket_id' => 3, 'nama_pilihan' => 'Bistik', 'urutan' => 3],
                // Paket A — Olahan Tambahan (komponen 4)
                ['id' => 7, 'komponen_paket_id' => 4, 'nama_pilihan' => 'Dori Asam Manis', 'urutan' => 1],
                ['id' => 8, 'komponen_paket_id' => 4, 'nama_pilihan' => 'Dori Saus Mentega', 'urutan' => 2],
                ['id' => 9, 'komponen_paket_id' => 4, 'nama_pilihan' => 'Sambal Goreng Ati Kentang', 'urutan' => 3],
                // Paket A — Sayuran (komponen 5)
                ['id' => 10, 'komponen_paket_id' => 5, 'nama_pilihan' => 'Salad Buah', 'urutan' => 1],
                ['id' => 11, 'komponen_paket_id' => 5, 'nama_pilihan' => 'Salad Sayuran', 'urutan' => 2],
                ['id' => 12, 'komponen_paket_id' => 5, 'nama_pilihan' => 'Gado-gado', 'urutan' => 3],
                ['id' => 13, 'komponen_paket_id' => 5, 'nama_pilihan' => 'Rujak', 'urutan' => 4],
                // Paket A — Stall (komponen 8)
                ['id' => 14, 'komponen_paket_id' => 8, 'nama_pilihan' => 'Bakso Tahu', 'urutan' => 1],
                ['id' => 15, 'komponen_paket_id' => 8, 'nama_pilihan' => 'Mi Kocok', 'urutan' => 2],
                // Paket A — Dessert (komponen 9)
                ['id' => 16, 'komponen_paket_id' => 9, 'nama_pilihan' => 'Buah Potong', 'urutan' => 1],
                ['id' => 17, 'komponen_paket_id' => 9, 'nama_pilihan' => 'Es Krim', 'urutan' => 2],
                // Paket B — Aneka Sup (komponen 11)
                ['id' => 18, 'komponen_paket_id' => 11, 'nama_pilihan' => 'Sup Kimlo', 'urutan' => 1],
                ['id' => 19, 'komponen_paket_id' => 11, 'nama_pilihan' => 'Sup Bakso', 'urutan' => 2],
                ['id' => 20, 'komponen_paket_id' => 11, 'nama_pilihan' => 'Sup Sosis', 'urutan' => 3],
                // Paket B — Olahan Ayam (komponen 12)
                ['id' => 21, 'komponen_paket_id' => 12, 'nama_pilihan' => 'Ayam Teriyaki', 'urutan' => 1],
                ['id' => 22, 'komponen_paket_id' => 12, 'nama_pilihan' => 'Ayam Suwir', 'urutan' => 2],
                ['id' => 23, 'komponen_paket_id' => 12, 'nama_pilihan' => 'Ayam Rica-rica', 'urutan' => 3],
                // Paket B — Olahan Tambahan (komponen 13)
                ['id' => 24, 'komponen_paket_id' => 13, 'nama_pilihan' => 'Dori Asam Manis', 'urutan' => 1],
                ['id' => 25, 'komponen_paket_id' => 13, 'nama_pilihan' => 'Dori Saus Mentega', 'urutan' => 2],
                ['id' => 26, 'komponen_paket_id' => 13, 'nama_pilihan' => 'Sambal Goreng Ati Kentang', 'urutan' => 3],
                // Paket B — Sayuran (komponen 14)
                ['id' => 27, 'komponen_paket_id' => 14, 'nama_pilihan' => 'Salad Buah', 'urutan' => 1],
                ['id' => 28, 'komponen_paket_id' => 14, 'nama_pilihan' => 'Salad Sayuran', 'urutan' => 2],
                ['id' => 29, 'komponen_paket_id' => 14, 'nama_pilihan' => 'Gado-gado', 'urutan' => 3],
                ['id' => 30, 'komponen_paket_id' => 14, 'nama_pilihan' => 'Rujak Buah', 'urutan' => 4],
                // Paket B — Stall (komponen 17)
                ['id' => 31, 'komponen_paket_id' => 17, 'nama_pilihan' => 'Bakso Tahu', 'urutan' => 1],
                ['id' => 32, 'komponen_paket_id' => 17, 'nama_pilihan' => 'Mi Kocok', 'urutan' => 2],
                // Paket B — Dessert (komponen 18)
                ['id' => 33, 'komponen_paket_id' => 18, 'nama_pilihan' => 'Buah Potong', 'urutan' => 1],
                ['id' => 34, 'komponen_paket_id' => 18, 'nama_pilihan' => 'Es Krim', 'urutan' => 2],
            ];
            foreach ($pilihanList as $p) {
                DB::table('pilihan_komponen_paket')->updateOrInsert(['id' => $p['id']], $p);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
