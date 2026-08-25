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
        DB::table('status_pesanan')->updateOrInsert(
            ['id' => 8],
            [
                'kode_status' => 'DIHIDANGKAN',
                'nama_status' => 'Pesanan Telah Dihidangkan'
            ]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('status_pesanan')->where('id', 8)->delete();
    }
};
