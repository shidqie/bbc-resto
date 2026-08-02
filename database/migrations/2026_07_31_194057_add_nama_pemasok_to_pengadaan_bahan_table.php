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
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->string('nama_pemasok', 150)->nullable()->after('nomor_pengadaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->dropColumn('nama_pemasok');
        });
    }
};
