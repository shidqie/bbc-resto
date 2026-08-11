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
        // 1. Pengguna
        Schema::table('pengguna', function (Blueprint $table) {
            $table->string('id_pengguna', 20)->nullable()->after('id')->unique();
        });

        // 2. Menu
        Schema::table('menu', function (Blueprint $table) {
            $table->renameColumn('kode_menu', 'id_menu');
        });

        // 3. Bahan Baku
        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->renameColumn('kode_bahan', 'id_bahan_baku');
        });

        // 4. Pesanan
        Schema::table('pesanan', function (Blueprint $table) {
            $table->renameColumn('nomor_pesanan', 'id_pesanan');
        });

        // 5. Pengadaan Bahan
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->renameColumn('nomor_pengadaan', 'id_pengadaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->renameColumn('id_pengadaan', 'nomor_pengadaan');
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->renameColumn('id_pesanan', 'nomor_pesanan');
        });

        Schema::table('bahan_baku', function (Blueprint $table) {
            $table->renameColumn('id_bahan_baku', 'kode_bahan');
        });

        Schema::table('menu', function (Blueprint $table) {
            $table->renameColumn('id_menu', 'kode_menu');
        });

        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn('id_pengguna');
        });
    }
};
