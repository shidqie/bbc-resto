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
        // Alter mutasi_stoks
        Schema::table('mutasi_stoks', function (Blueprint $table) {
            $table->string('referensi')->nullable()->after('jumlah');
        });

        // Alter pengadaans
        Schema::table('pengadaans', function (Blueprint $table) {
            $table->string('nomor_pengadaan')->nullable()->after('id');
            $table->string('asal_pembelian')->nullable()->after('nomor_pengadaan');
            
            // Drop unused columns if they exist
            if (Schema::hasColumn('pengadaans', 'kode_pengadaan')) {
                $table->dropColumn('kode_pengadaan');
            }
            if (Schema::hasColumn('pengadaans', 'pesanan_catering_id')) {
                $table->dropForeign(['pesanan_catering_id']);
                $table->dropColumn('pesanan_catering_id');
            }
            if (Schema::hasColumn('pengadaans', 'pesanan_nasi_box_id')) {
                $table->dropForeign(['pesanan_nasi_box_id']);
                $table->dropColumn('pesanan_nasi_box_id');
            }
            if (Schema::hasColumn('pengadaans', 'jenis_pesanan')) {
                $table->dropColumn('jenis_pesanan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mutasi_stoks', function (Blueprint $table) {
            $table->dropColumn('referensi');
        });

        Schema::table('pengadaans', function (Blueprint $table) {
            $table->dropColumn('nomor_pengadaan');
            $table->dropColumn('asal_pembelian');
            $table->string('kode_pengadaan')->nullable();
            $table->string('jenis_pesanan')->nullable();
        });
    }
};
