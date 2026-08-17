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
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dateTime('expires_at')->nullable()->after('catatan_verifikasi');
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->dateTime('batas_pelunasan')->nullable()->after('catatan');
        });

        // Pastikan status_pesanan memiliki status 'perlu_tinjauan_pemilik' (kode: TINJAU)
        DB::table('status_pesanan')->insertOrIgnore([
            ['id' => 7, 'kode_status' => 'TINJAU', 'nama_status' => 'Perlu Tinjauan Pemilik'] // asumsikan id 7 aman
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('expires_at');
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn('batas_pelunasan');
        });

        DB::table('status_pesanan')->where('kode_status', 'TINJAU')->delete();
    }
};
