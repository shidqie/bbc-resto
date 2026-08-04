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
        for ($i = 1; $i <= 20; $i++) {
            if ($i <= 5) {
                $area = 'Indoor';
                $kapasitas = 4;
            } elseif ($i <= 10) {
                $area = 'Indoor';
                $kapasitas = 6;
            } elseif ($i <= 15) {
                $area = 'Outdoor';
                $kapasitas = 4;
            } else {
                $area = 'Outdoor';
                $kapasitas = 6;
            }

            Meja::updateOrCreate(
                ['nomor_meja' => 'Meja '.str_pad($i, 2, '0', STR_PAD_LEFT)],
                [
                    'area' => $area,
                    'kapasitas' => $kapasitas,
                    'status_meja_id' => 1, // Tersedia
                ]
            );
        }

        $this->command->info('20 meja (Indoor & Outdoor) berhasil disiapkan.');
    }
}
