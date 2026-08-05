<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusPengadaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'kode_status' => 'menunggu_pembelian', 'nama_status' => 'Menunggu Pembelian'],
            ['id' => 2, 'kode_status' => 'telah_dipesan', 'nama_status' => 'Telah Dipesan'],
            ['id' => 3, 'kode_status' => 'diterima_sebagian', 'nama_status' => 'Diterima Sebagian'],
            ['id' => 4, 'kode_status' => 'selesai', 'nama_status' => 'Selesai'],
            ['id' => 5, 'kode_status' => 'dibatalkan', 'nama_status' => 'Dibatalkan'],
        ];

        foreach ($statuses as $status) {
            \App\Models\StatusPengadaan::updateOrCreate(
                ['id' => $status['id']],
                $status
            );
        }
    }
}
