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
        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->dropForeign(['pengadaan_bahan_id']);
            $table->dropColumn('pengadaan_bahan_id');
        });

        Schema::table('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->dropForeign(['detail_pengadaan_bahan_id']);
            $table->dropColumn('detail_pengadaan_bahan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->foreignId('pengadaan_bahan_id')->nullable()->constrained('pengadaan_bahan');
        });

        Schema::table('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->foreignId('detail_pengadaan_bahan_id')->nullable()->constrained('detail_pengadaan_bahan');
        });
    }
};
