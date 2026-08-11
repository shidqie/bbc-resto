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
        Schema::table('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->string('nama_supplier', 150)->nullable()->after('harga_satuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->dropColumn('nama_supplier');
        });
    }
};
