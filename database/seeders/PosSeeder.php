<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\User;

class PosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed User Kasir
        User::firstOrCreate(
            ['email' => 'kasir@bbc-resto.com'],
            [
                'name' => 'Kasir F&B',
                'password' => bcrypt('password'),
            ]
        );

        $makanan = KategoriMenu::create(['nama' => 'Makanan']);
        $minuman = KategoriMenu::create(['nama' => 'Minuman']);
        $snack = KategoriMenu::create(['nama' => 'Snack']);

        Menu::create(['kategori_menu_id' => $makanan->id, 'nama' => 'Nasi Goreng Spesial', 'harga' => 25000]);
        Menu::create(['kategori_menu_id' => $makanan->id, 'nama' => 'Ayam Bakar Madu', 'harga' => 30000]);
        Menu::create(['kategori_menu_id' => $makanan->id, 'nama' => 'Sate Ayam Madura', 'harga' => 20000]);
        Menu::create(['kategori_menu_id' => $makanan->id, 'nama' => 'Soto Ayam Lamongan', 'harga' => 18000]);

        Menu::create(['kategori_menu_id' => $minuman->id, 'nama' => 'Es Teh Manis', 'harga' => 5000]);
        Menu::create(['kategori_menu_id' => $minuman->id, 'nama' => 'Es Jeruk Peras', 'harga' => 8000]);
        Menu::create(['kategori_menu_id' => $minuman->id, 'nama' => 'Kopi Susu Gula Aren', 'harga' => 15000]);

        Menu::create(['kategori_menu_id' => $snack->id, 'nama' => 'Pisang Goreng Keju', 'harga' => 12000]);
        Menu::create(['kategori_menu_id' => $snack->id, 'nama' => 'Kentang Goreng', 'harga' => 15000]);
        Menu::create(['kategori_menu_id' => $snack->id, 'nama' => 'Roti Bakar Coklat', 'harga' => 14000]);
    }
}
