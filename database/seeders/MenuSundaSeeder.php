<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSundaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Bersihkan data lama
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('detail_pesanans')->truncate();
        DB::table('pesanans')->truncate();
        DB::table('menus')->truncate();
        DB::table('kategori_menus')->truncate();
        DB::table('paket_caterings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Kategori Menu Dine-in
        $kategoriList = [
            ['id' => 1, 'nama' => 'Paket Nasi Ayam'],
            ['id' => 2, 'nama' => 'Paket Nasi Ayam Kampung'],
            ['id' => 3, 'nama' => 'Paket Nasi Bebek'],
            ['id' => 4, 'nama' => 'Lauk Satuan'],
            ['id' => 5, 'nama' => 'Sayur dan Lalapan'],
            ['id' => 6, 'nama' => 'Tambahan'],
            ['id' => 7, 'nama' => 'Minuman Kopi Susu'],
            ['id' => 8, 'nama' => 'Classic Coffee'],
            ['id' => 9, 'nama' => 'Manual Brew Coffee'],
        ];
        
        DB::table('kategori_menus')->insert($kategoriList);

        // 2. Menu Dine-in
        $menus = [
            // Paket Nasi Ayam
            ['kategori_menu_id' => 1, 'nama' => 'Nasi Ayam Goreng / Bakar', 'harga' => 26000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 1, 'nama' => 'Liwet Ayam Goreng / Bakar', 'harga' => 27000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 1, 'nama' => 'Nasi Ayam Penyet Goreng / Bakar', 'harga' => 27000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 1, 'nama' => 'Liwet Ayam Penyet Goreng / Bakar', 'harga' => 28000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 1, 'nama' => 'Nasi TO Ayam Goreng / Bakar', 'harga' => 27000, 'jenis_menu' => 'dine_in'],

            // Paket Nasi Ayam Kampung
            ['kategori_menu_id' => 2, 'nama' => 'Nasi Ayam Kampung Goreng / Bakar', 'harga' => 32000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 2, 'nama' => 'Liwet Ayam Kampung Goreng / Bakar', 'harga' => 34000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 2, 'nama' => 'Nasi Ayam Kampung Penyet Goreng / Bakar', 'harga' => 33000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 2, 'nama' => 'Liwet Ayam Kampung Penyet Goreng / Bakar', 'harga' => 34000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 2, 'nama' => 'Nasi TO Ayam Kampung Goreng / Bakar', 'harga' => 34000, 'jenis_menu' => 'dine_in'],

            // Paket Nasi Bebek
            ['kategori_menu_id' => 3, 'nama' => 'Nasi Bebek Goreng / Bakar', 'harga' => 60000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 3, 'nama' => 'Liwet Bebek Penyet Goreng / Bakar', 'harga' => 61000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 3, 'nama' => 'Nasi Bebek Penyet Goreng / Bakar', 'harga' => 61000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 3, 'nama' => 'Liwet Bebek Goreng / Bakar', 'harga' => 63000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 3, 'nama' => 'Nasi TO Bebek Goreng / Bakar', 'harga' => 63000, 'jenis_menu' => 'dine_in'],

            // Lauk Satuan
            ['kategori_menu_id' => 4, 'nama' => 'Ayam Bakar', 'harga' => 23000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 4, 'nama' => 'Ayam Kampung', 'harga' => 33000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 4, 'nama' => 'Bebek', 'harga' => 60000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 4, 'nama' => 'Tahu / Tempe', 'harga' => 4000, 'jenis_menu' => 'dine_in'],

            // Sayur dan Lalapan
            ['kategori_menu_id' => 5, 'nama' => 'Jengkol', 'harga' => 13000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 5, 'nama' => 'Pete', 'harga' => 13000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 5, 'nama' => 'Peda', 'harga' => 13000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 5, 'nama' => 'Sepat', 'harga' => 14000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 5, 'nama' => 'Karedok / Jukut Goreng', 'harga' => 13000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 5, 'nama' => 'Karedok', 'harga' => 15000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 5, 'nama' => 'Lotek', 'harga' => 15000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 5, 'nama' => 'Pencok Kacang', 'harga' => 15000, 'jenis_menu' => 'dine_in'],

            // Tambahan
            ['kategori_menu_id' => 6, 'nama' => 'Nasi', 'harga' => 7000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 6, 'nama' => 'Nasi Pulen', 'harga' => 13000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 6, 'nama' => 'Liwet / Nasi TO', 'harga' => 9000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 6, 'nama' => 'Liwet Pulen / Nasi TO Pulen', 'harga' => 14000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 6, 'nama' => 'Sambal', 'harga' => 6000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 6, 'nama' => 'Lalab Sambal', 'harga' => 7000, 'jenis_menu' => 'dine_in'],

            // Minuman Kopi Susu
            ['kategori_menu_id' => 7, 'nama' => 'Es Kopi Susu', 'harga' => 20000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 7, 'nama' => 'Es Kopi Susu Vanilla', 'harga' => 20000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 7, 'nama' => 'Es Kopi Susu Gula Aren', 'harga' => 20000, 'jenis_menu' => 'dine_in'],

            // Classic Coffee
            ['kategori_menu_id' => 8, 'nama' => 'Americano', 'harga' => 20000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 8, 'nama' => 'Cappuccino', 'harga' => 20000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 8, 'nama' => 'Cafe Latte', 'harga' => 20000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 8, 'nama' => 'Espresso', 'harga' => 15000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 8, 'nama' => 'Caramel Macchiato', 'harga' => 20000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 8, 'nama' => 'Hot Green Matcha', 'harga' => 20000, 'jenis_menu' => 'dine_in'],

            // Manual Brew Coffee
            ['kategori_menu_id' => 9, 'nama' => 'Kopi Tubruk Arabika', 'harga' => 15000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 9, 'nama' => 'Kopi Tubruk Robusta', 'harga' => 15000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 9, 'nama' => 'V60', 'harga' => 20000, 'jenis_menu' => 'dine_in'],
            ['kategori_menu_id' => 9, 'nama' => 'Vietnam Drip', 'harga' => 20000, 'jenis_menu' => 'dine_in'],
        ];

        foreach ($menus as $menu) {
            DB::table('menus')->insert(array_merge($menu, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // 3. Paket Catering
        $caterings = [
            [
                'nama_paket' => 'Paket A',
                'jenis_paket' => 'catering',
                'harga' => 47500,
                'deskripsi' => "Nasi putih\nAneka sup (Kimlo/Bakso/Ayam Sosis)\nOlahan Sapi (Teriyaki/Rendang/Bistik)\nOlahan Tambahan (Dori Asam Manis/Dori Saus Mentega/Sambal Goreng Ati Kentang)\nSayuran (Salad/Gado-gado/Rujak)\nKerupuk udang, Air mineral\nStall (Bakso Tahu/Mi Kocok)\nDessert (Buah Potong/Es Krim)",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nama_paket' => 'Paket B',
                'jenis_paket' => 'catering',
                'harga' => 42500,
                'deskripsi' => "Nasi putih\nAneka sup (Kimlo/Bakso/Sosis)\nOlahan Ayam (Teriyaki/Suwir/Rica-rica)\nOlahan Tambahan (Dori Asam Manis/Dori Saus Mentega/Sambal Goreng Ati Kentang)\nSayuran (Salad/Gado-gado/Rujak Buah)\nKerupuk udang, Air mineral\nStall (Bakso Tahu/Mi Kocok)\nDessert (Buah Potong/Es Krim)",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('paket_caterings')->insert($caterings);
    }
}
