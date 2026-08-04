<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan jenis_persediaan (harian/catering) pada mutasi_stok dan
 * detail_penyesuaian_stok, lalu normalkan nilai jenis_pengadaan agar
 * konsisten memakai huruf kecil sesuai jenis persediaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Mutasi stok wajib menyimpan jenis persediaan yang dipengaruhi.
        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->enum('jenis_persediaan', ['harian', 'catering'])
                ->default('harian')
                ->after('tanggal_mutasi');
        });

        // Backfill dari kolom lama jenis_stok (OPERASIONAL -> harian, CATERING -> catering).
        DB::table('mutasi_stok')->where('jenis_stok', 'CATERING')
            ->update(['jenis_persediaan' => 'catering']);

        // Penyesuaian stok juga dilacak per jenis persediaan.
        Schema::table('detail_penyesuaian_stok', function (Blueprint $table) {
            $table->enum('jenis_persediaan', ['harian', 'catering'])
                ->default('harian')
                ->after('jumlah_selisih');
        });

        // Normalisasi nilai jenis_pengadaan: OPERASIONAL/REGULER -> harian, CATERING -> catering.
        DB::table('pengadaan_bahan')->whereIn('jenis_pengadaan', ['OPERASIONAL', 'REGULER', 'Reguler'])
            ->update(['jenis_pengadaan' => 'harian']);
        DB::table('pengadaan_bahan')->where('jenis_pengadaan', 'CATERING')
            ->update(['jenis_pengadaan' => 'catering']);

        // Notifikasi stok dilacak per jenis persediaan.
        Schema::table('notifikasi_stoks', function (Blueprint $table) {
            $table->enum('jenis_persediaan', ['harian', 'catering'])
                ->default('harian')
                ->after('jenis');
        });
    }

    public function down(): void
    {
        Schema::table('mutasi_stok', function (Blueprint $table) {
            $table->dropColumn('jenis_persediaan');
        });

        Schema::table('detail_penyesuaian_stok', function (Blueprint $table) {
            $table->dropColumn('jenis_persediaan');
        });

        Schema::table('notifikasi_stoks', function (Blueprint $table) {
            $table->dropColumn('jenis_persediaan');
        });

        // Kembalikan nilai lama (tidak dapat dibedakan harian/reguler asli).
        DB::table('pengadaan_bahan')->where('jenis_pengadaan', 'harian')
            ->update(['jenis_pengadaan' => 'OPERASIONAL']);
        DB::table('pengadaan_bahan')->where('jenis_pengadaan', 'catering')
            ->update(['jenis_pengadaan' => 'CATERING']);
    }
};
