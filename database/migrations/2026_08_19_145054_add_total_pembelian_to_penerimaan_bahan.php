<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->decimal('total_pembelian', 15, 2)->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->dropColumn('total_pembelian');
        });
    }
};
