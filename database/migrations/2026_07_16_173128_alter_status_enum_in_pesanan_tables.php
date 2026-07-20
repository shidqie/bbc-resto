<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pesanan_caterings MODIFY COLUMN status ENUM('menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'diproses', 'dikirim', 'selesai', 'lunas', 'dibatalkan') DEFAULT 'menunggu_dp'");
        DB::statement("ALTER TABLE pesanan_nasi_boxes MODIFY COLUMN status ENUM('menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'diproses', 'dikirim', 'selesai', 'lunas', 'dibatalkan') DEFAULT 'menunggu_dp'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pesanan_caterings MODIFY COLUMN status ENUM('menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'lunas', 'dibatalkan') DEFAULT 'menunggu_dp'");
        DB::statement("ALTER TABLE pesanan_nasi_boxes MODIFY COLUMN status ENUM('menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'lunas', 'dibatalkan') DEFAULT 'menunggu_dp'");
    }
};
