<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusPengadaanSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['id' => 1, 'kode_status' => 'draft', 'nama_status' => 'Draft'],
            ['id' => 2, 'kode_status' => 'menunggu_pembelian', 'nama_status' => 'Menunggu Pembelian'],
            ['id' => 3, 'kode_status' => 'dalam_proses', 'nama_status' => 'Dalam Proses'],
            ['id' => 4, 'kode_status' => 'menunggu_penerimaan', 'nama_status' => 'Menunggu Penerimaan'],
            ['id' => 5, 'kode_status' => 'diterima_sebagian', 'nama_status' => 'Diterima Sebagian'],
            ['id' => 6, 'kode_status' => 'selesai', 'nama_status' => 'Selesai'],
            ['id' => 7, 'kode_status' => 'dibatalkan', 'nama_status' => 'Dibatalkan'],
        ];

        foreach ($statuses as $status) {
            \App\Models\StatusPengadaan::updateOrCreate(
                ['id' => $status['id']],
                $status
            );
        }
    }
}
