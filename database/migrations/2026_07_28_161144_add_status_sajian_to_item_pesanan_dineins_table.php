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
        Schema::table('item_pesanan_dineins', function (Blueprint $table) {
            $table->boolean('status_sajian')->default(false)->after('qty')->comment('0 = belum disajikan, 1 = sudah disajikan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('item_pesanan_dineins', function (Blueprint $table) {
            $table->dropColumn('status_sajian');
        });
    }
};
