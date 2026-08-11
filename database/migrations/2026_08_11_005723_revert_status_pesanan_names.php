<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('status_pesanan')->truncate();
        DB::table('status_pesanan')->insert([
            ['id' => 1, 'kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Konfirmasi'],
            ['id' => 2, 'kode_status' => 'DIKONFIRMASI', 'nama_status' => 'Dikonfirmasi'],
            ['id' => 3, 'kode_status' => 'DIPROSES', 'nama_status' => 'Sedang Diproses'],
            ['id' => 4, 'kode_status' => 'SIAP', 'nama_status' => 'Siap Dikirim'],
            ['id' => 5, 'kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
            ['id' => 6, 'kode_status' => 'DIBATALKAN', 'nama_status' => 'Dibatalkan'],
        ]);

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        DB::table('status_pesanan')->truncate();
        DB::table('status_pesanan')->insert([
            ['id' => 1, 'kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Konfirmasi'],
            ['id' => 2, 'kode_status' => 'DIPROSES', 'nama_status' => 'Diproses'],
            ['id' => 3, 'kode_status' => 'PENGANTARAN', 'nama_status' => 'Dalam Pengantaran'],
            ['id' => 4, 'kode_status' => 'SIAP', 'nama_status' => 'Siap Diambil'],
            ['id' => 5, 'kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
            ['id' => 6, 'kode_status' => 'DIBATALKAN', 'nama_status' => 'Dibatalkan'],
        ]);

        Schema::enableForeignKeyConstraints();
    }
};
