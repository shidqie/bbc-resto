<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;

class DummyMenuSeeder extends Seeder
{
    public function run(): void
    {
        // Kategori
        $makanan = Category::firstOrCreate(['name' => 'Makanan']);
        $minuman = Category::firstOrCreate(['name' => 'Minuman']);
        $snack = Category::firstOrCreate(['name' => 'Cemilan']);

        // Dummy Products
        $products = [
            [
                'category_id' => $makanan->id,
                'name' => 'Nasi Goreng Spesial',
                'description' => 'Nasi goreng dengan telur, ayam, dan sosis.',
                'price' => 25000,
                'is_dine_in' => true,
                'is_catering' => true,
                'is_nasi_box' => true,
            ],
            [
                'category_id' => $makanan->id,
                'name' => 'Mie Goreng Seafood',
                'description' => 'Mie goreng dengan udang dan cumi.',
                'price' => 30000,
                'is_dine_in' => true,
                'is_catering' => true,
                'is_nasi_box' => true,
            ],
            [
                'category_id' => $minuman->id,
                'name' => 'Es Teh Manis',
                'description' => 'Es teh segar dan manis.',
                'price' => 5000,
                'is_dine_in' => true,
                'is_catering' => false,
                'is_nasi_box' => true,
            ],
            [
                'category_id' => $minuman->id,
                'name' => 'Es Jeruk',
                'description' => 'Perasan jeruk asli yang segar.',
                'price' => 8000,
                'is_dine_in' => true,
                'is_catering' => false,
                'is_nasi_box' => true,
            ],
            [
                'category_id' => $snack->id,
                'name' => 'Kentang Goreng',
                'description' => 'Kentang goreng renyah.',
                'price' => 15000,
                'is_dine_in' => true,
                'is_catering' => false,
                'is_nasi_box' => false,
            ],
            [
                'category_id' => $makanan->id,
                'name' => 'Ayam Penyet',
                'description' => 'Ayam penyet dengan sambal terasi khas.',
                'price' => 22000,
                'is_dine_in' => true,
                'is_catering' => true,
                'is_nasi_box' => true,
            ],
            [
                'category_id' => $makanan->id,
                'name' => 'Sate Ayam',
                'description' => 'Sate ayam bumbu kacang.',
                'price' => 20000,
                'is_dine_in' => true,
                'is_catering' => true,
                'is_nasi_box' => true,
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
