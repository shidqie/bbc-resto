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
        // 1. Rename tables
        Schema::rename('status_pengantaran', 'status_pengiriman');
        Schema::rename('pengantaran', 'pengiriman');

        // 2. Rename columns in pengiriman
        Schema::table('pengiriman', function (Blueprint $table) {
            $table->renameColumn('nomor_pengantaran', 'nomor_pengiriman');
            $table->renameColumn('status_pengantaran_id', 'status_pengiriman_id');
            $table->renameColumn('foto_bukti_pengantaran', 'foto_bukti_pengiriman');
            $table->renameColumn('jadwal_pengantaran', 'jadwal_pengiriman');
            $table->renameColumn('alamat_pengantaran', 'alamat_pengiriman');
            $table->renameColumn('jarak_pengantaran', 'jarak_pengiriman');
            $table->renameColumn('biaya_pengantaran', 'biaya_pengiriman');
        });

        // 3. Rename columns in jadwal_pesanan
        Schema::table('jadwal_pesanan', function (Blueprint $table) {
            $table->renameColumn('waktu_pengantaran', 'waktu_pengiriman');
            $table->renameColumn('alamat_pengantaran', 'alamat_pengiriman');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pesanan', function (Blueprint $table) {
            $table->renameColumn('waktu_pengiriman', 'waktu_pengantaran');
            $table->renameColumn('alamat_pengiriman', 'alamat_pengantaran');
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->renameColumn('nomor_pengiriman', 'nomor_pengantaran');
            $table->renameColumn('status_pengiriman_id', 'status_pengantaran_id');
            $table->renameColumn('foto_bukti_pengiriman', 'foto_bukti_pengantaran');
            $table->renameColumn('jadwal_pengiriman', 'jadwal_pengantaran');
            $table->renameColumn('alamat_pengiriman', 'alamat_pengantaran');
            $table->renameColumn('jarak_pengiriman', 'jarak_pengantaran');
            $table->renameColumn('biaya_pengiriman', 'biaya_pengantaran');
        });

        Schema::rename('pengiriman', 'pengantaran');
        Schema::rename('status_pengiriman', 'status_pengantaran');
    }
};
