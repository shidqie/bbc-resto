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
        Schema::table('pesanan_dineins', function (Blueprint $table) {
            $table->string('nama_konsumen')->nullable()->after('meja_id');
            $table->integer('jumlah_tamu')->nullable()->after('nama_konsumen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_dineins', function (Blueprint $table) {
            $table->dropColumn(['nama_konsumen', 'jumlah_tamu']);
        });
    }
};
