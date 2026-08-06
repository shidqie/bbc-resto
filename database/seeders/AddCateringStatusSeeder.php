<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddCateringStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $newStatuses = [
            ['kode_status' => 'MENUNGGU_PEMBAYARAN', 'nama_status' => 'Menunggu Pembayaran'],
            ['kode_status' => 'TERKONFIRMASI_CATERING', 'nama_status' => 'Terkonfirmasi'],
            ['kode_status' => 'PROSES_PENGADAAN', 'nama_status' => 'Proses Pengadaan'],
            ['kode_status' => 'BAHAN_DITERIMA', 'nama_status' => 'Bahan Diterima'],
            ['kode_status' => 'SEDANG_PRODUKSI', 'nama_status' => 'Sedang Produksi'],
            ['kode_status' => 'PRODUKSI_SELESAI', 'nama_status' => 'Produksi Selesai'],
        ];

        foreach ($newStatuses as $status) {
            DB::table('status_pesanan')->updateOrInsert(
                ['kode_status' => $status['kode_status']],
                ['nama_status' => $status['nama_status']]
            );
        }
    }
}
