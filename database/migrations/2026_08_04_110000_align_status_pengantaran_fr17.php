<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * FR-17 — Status pengantaran mengikuti PRD:
 * dijadwalkan, siap_dikirim, dalam_perjalanan, diterima, gagal_dikirim.
 * Menyesuaikan seed lama (MENUNGGU/DIJALAN/SELESAI/BATAL) tanpa mengubah id.
 */
return new class extends Migration
{
    public function up(): void
    {
        $statuses = [
            1 => ['kode_status' => 'DIJADWALKAN', 'nama_status' => 'Dijadwalkan'],
            2 => ['kode_status' => 'SIAP_DIKIRIM', 'nama_status' => 'Siap Dikirim'],
            3 => ['kode_status' => 'DALAM_PERJALANAN', 'nama_status' => 'Dalam Perjalanan'],
            4 => ['kode_status' => 'DITERIMA', 'nama_status' => 'Diterima'],
            5 => ['kode_status' => 'GAGAL_DIKIRIM', 'nama_status' => 'Gagal Dikirim'],
        ];

        foreach ($statuses as $id => $data) {
            DB::table('status_pengantaran')->updateOrInsert(
                ['id' => $id],
                $data
            );
        }
    }

    public function down(): void
    {
        DB::table('status_pengantaran')->where('id', 5)->delete();
    }
};
