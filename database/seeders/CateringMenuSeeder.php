<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class CateringMenuSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = Category::firstOrCreate(['name' => 'Catering']);

        $paketA = "Nasi putih\nAneka sop (pilih 1): Sop kimlo, Sop baso, Sop ayam sosis\nAneka olahan daging sapi (pilih 1): Sapi teriyaki, Rendang, Bistik\nAneka olahan ikan (pilih 1): Dori asam manis, Dori saus mentega\nSambal goreng ati kentang\nSayuran (pilih 1): Salad buah, Salad sayuran, Gado-gado, Rujak\nKerupuk udang\nAir mineral\nStall (pilih 1): Baso tahu, Mie kocok\nDessert (pilih 1): Buah potong, Es goyobod";

        $paketB = "Nasi putih\nAneka sop (pilih 1): Sop kimlo, Sop baso, Sop sosis\nAneka olahan ayam (pilih 1): Ayam teriyaki, Ayam suir, Ayam rica-rica\nAneka olahan ikan (pilih 1): Dori asam manis, Dori saus mentega\nSambal goreng ati kentang\nSayuran (pilih 1): Salad buah, Salad sayuran, Gado-gado, Rujak buah\nKerupuk udang\nAir mineral\nStall (pilih 1): Baso tahu, Mie kocok\nDessert (pilih 1): Buah potong, Es goyobod";

        Product::create([
            'category_id' => $kategori->id,
            'name' => 'Paket Catering A',
            'description' => $paketA,
            'price' => 50000,
            'is_dine_in' => false,
            'is_catering' => true,
            'is_nasi_box' => false,
        ]);

        Product::create([
            'category_id' => $kategori->id,
            'name' => 'Paket Catering B',
            'description' => $paketB,
            'price' => 45000,
            'is_dine_in' => false,
            'is_catering' => true,
            'is_nasi_box' => false,
        ]);
    }
}
