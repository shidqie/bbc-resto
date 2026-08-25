<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Status Pesanan (Multiaktor RM BBC)
        $statusPesanan = [
            1 => ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu Konfirmasi'],
            2 => ['kode_status' => 'DIKONFIRMASI', 'nama_status' => 'Dikonfirmasi'],
            3 => ['kode_status' => 'DIPROSES', 'nama_status' => 'Sedang Diproses'],
            4 => ['kode_status' => 'SIAP', 'nama_status' => 'Pesanan Siap'],
            5 => ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
            6 => ['kode_status' => 'DIBATALKAN', 'nama_status' => 'Dibatalkan'],
            7 => ['kode_status' => 'TERJADWAL', 'nama_status' => 'Terjadwal'],
        ];
        foreach ($statusPesanan as $id => $data) {
            DB::table('status_pesanan')->updateOrInsert(['id' => $id], $data);
        }

        // 2. Status Pembayaran
        $statusPembayaran = [
            1 => ['kode_status' => 'BELUM_BAYAR', 'nama_status' => 'Belum Bayar'],
            2 => ['kode_status' => 'MENUNGGU_VERIFIKASI', 'nama_status' => 'Menunggu Verifikasi'],
            3 => ['kode_status' => 'DP_TERVERIFIKASI', 'nama_status' => 'DP Terverifikasi'],
            4 => ['kode_status' => 'MENUNGGU_PELUNASAN', 'nama_status' => 'Menunggu Pelunasan'],
            5 => ['kode_status' => 'LUNAS', 'nama_status' => 'Lunas'],
            6 => ['kode_status' => 'DITOLAK', 'nama_status' => 'Pembayaran Ditolak'],
        ];
        foreach ($statusPembayaran as $id => $data) {
            DB::table('status_pembayaran')->updateOrInsert(['id' => $id], $data);
        }

        // 3. Status Pengiriman
        $statusPengiriman = [
            1 => ['kode_status' => 'DIJADWALKAN', 'nama_status' => 'Dijadwalkan'],
            2 => ['kode_status' => 'SIAP_DIKIRIM', 'nama_status' => 'Siap Dikirim'],
            3 => ['kode_status' => 'DALAM_PENGANTARAN', 'nama_status' => 'Dalam Pengantaran'],
            4 => ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai'],
            5 => ['kode_status' => 'DIBATALKAN', 'nama_status' => 'Dibatalkan'],
        ];
        foreach ($statusPengiriman as $id => $data) {
            DB::table('status_pengiriman')->updateOrInsert(['id' => $id], $data);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep status records intact
    }
};
