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
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->string('status')->default('ditinjau')->comment('ditinjau, dikonfirmasi, diproses, menunggu_pengiriman, dikirim, selesai, dibatalkan')->change();
            $table->enum('status_bayar', ['belum_bayar', 'dp_terbayar', 'lunas'])->default('belum_bayar')->after('status');
        });

        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->string('status')->default('ditinjau')->comment('ditinjau, dikonfirmasi, diproses, menunggu_pengiriman, dikirim, selesai, dibatalkan')->change();
            $table->enum('status_bayar', ['belum_bayar', 'dp_terbayar', 'lunas'])->default('belum_bayar')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->enum('status', ['menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'diproses', 'dikirim', 'selesai', 'lunas', 'dibatalkan'])->default('menunggu_dp')->change();
            $table->dropColumn('status_bayar');
        });

        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->enum('status', ['menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'diproses', 'dikirim', 'selesai', 'lunas', 'dibatalkan'])->default('menunggu_dp')->change();
            $table->dropColumn('status_bayar');
        });
    }
};
