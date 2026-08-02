<?php

namespace Database\Seeders;

use App\Models\Meja;
use Illuminate\Database\Seeder;

class MejaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Meja::exists()) {
            return;
        }

        for ($i = 1; $i <= 25; $i++) {
            Meja::create([
                'nomor_meja' => 'Meja '.str_pad($i, 2, '0', STR_PAD_LEFT),
                'kapasitas' => 4,
                'status_meja_id' => 1, // Tersedia
            ]);
        }
    }
}
