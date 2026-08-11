<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // 1. Migrate Status Pesanan
        // Sebelumnya: 1: Menunggu Konfirmasi, 2: Dikonfirmasi, 3: Sedang Diproses, 4: Siap Disajikan / Diambil, 5: Selesai, 6: Dibatalkan
        // Menjadi: 1: Menunggu Konfirmasi, 2: Diproses, 3: Dalam Pengantaran, 4: Siap Diambil, 5: Selesai, 6: Dibatalkan

        DB::table('status_pesanan')->truncate();
        DB::table('status_pesanan')->insert([
            ['id' => 1, 'kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Konfirmasi'],
            ['id' => 2, 'kode_status' => 'DIPROSES', 'nama_status' => 'Diproses'],
            ['id' => 3, 'kode_status' => 'PENGANTARAN', 'nama_status' => 'Dalam Pengantaran'],
            ['id' => 4, 'kode_status' => 'SIAP', 'nama_status' => 'Siap Diambil'],
            ['id' => 5, 'kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
            ['id' => 6, 'kode_status' => 'DIBATALKAN', 'nama_status' => 'Dibatalkan'],
        ]);

        // 2. Migrate Status Pembayaran
        // Sebelumnya: 1: Menunggu Pembayaran, 2: Dibayar Sebagian, 3: Lunas, 4: Gagal, 5: Dikembalikan
        // Menjadi: 1: Menunggu Pembayaran DP, 2: Menunggu Verifikasi DP, 3: Menunggu Pelunasan, 4: Menunggu Verifikasi Pelunasan, 5: Lunas, 6: Ditolak

        DB::table('status_pembayaran')->truncate();
        DB::table('status_pembayaran')->insert([
            ['id' => 1, 'kode_status' => 'MENUNGGU_DP', 'nama_status' => 'Menunggu Pembayaran DP'],
            ['id' => 2, 'kode_status' => 'VERIFIKASI_DP', 'nama_status' => 'Menunggu Verifikasi DP'],
            ['id' => 3, 'kode_status' => 'MENUNGGU_LUNAS', 'nama_status' => 'Menunggu Pelunasan'],
            ['id' => 4, 'kode_status' => 'VERIFIKASI_LUNAS', 'nama_status' => 'Menunggu Verifikasi Pelunasan'],
            ['id' => 5, 'kode_status' => 'LUNAS', 'nama_status' => 'Lunas'],
            ['id' => 6, 'kode_status' => 'DITOLAK', 'nama_status' => 'Ditolak'],
        ]);
        // Tambahkan status_pembayaran_id ke pesanan jika belum ada
        if (!Schema::hasColumn('pesanan', 'status_pembayaran_id')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->foreignId('status_pembayaran_id')->default(1)->constrained('status_pembayaran')->after('status_pesanan_id');
            });
        }

        // Update pesanan yang ada yang mungkin memiliki status yang sudah tidak ada
        DB::table('pesanan')->where('status_pesanan_id', '>', 6)->update(['status_pesanan_id' => 1]);

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
            ['id' => 2, 'kode_status' => 'DIKONFIRMASI', 'nama_status' => 'Dikonfirmasi'],
            ['id' => 3, 'kode_status' => 'DIPROSES', 'nama_status' => 'Sedang Diproses'],
            ['id' => 4, 'kode_status' => 'SIAP', 'nama_status' => 'Siap Disajikan / Diambil'],
            ['id' => 5, 'kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
            ['id' => 6, 'kode_status' => 'DIBATALKAN', 'nama_status' => 'Dibatalkan'],
        ]);

        DB::table('status_pembayaran')->truncate();
        DB::table('status_pembayaran')->insert([
            ['id' => 1, 'kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Pembayaran'],
            ['id' => 2, 'kode_status' => 'SEBAGIAN', 'nama_status' => 'Dibayar Sebagian'],
            ['id' => 3, 'kode_status' => 'LUNAS', 'nama_status' => 'Lunas'],
            ['id' => 4, 'kode_status' => 'GAGAL', 'nama_status' => 'Gagal'],
            ['id' => 5, 'kode_status' => 'DIKEMBALIKAN', 'nama_status' => 'Dikembalikan'],
        ]);

        if (Schema::hasColumn('pesanan', 'status_pembayaran_id')) {
            Schema::table('pesanan', function (Blueprint $table) {
                $table->dropForeign(['status_pembayaran_id']);
                $table->dropColumn('status_pembayaran_id');
            });
        }

        Schema::enableForeignKeyConstraints();
    }
};
