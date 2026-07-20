<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MejaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            \App\Models\Meja::create([
                'nomor_meja' => 'Meja ' . $i,
                'kapasitas' => 4,
                'status' => 'kosong',
            ]);
        }
    }
}
