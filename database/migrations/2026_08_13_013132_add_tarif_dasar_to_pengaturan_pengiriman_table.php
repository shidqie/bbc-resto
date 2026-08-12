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
        Schema::table('pengaturan_pengiriman', function (Blueprint $table) {
            $table->decimal('tarif_dasar', 12, 2)->default(0)->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_pengiriman', function (Blueprint $table) {
            $table->dropColumn('tarif_dasar');
        });
    }
};
