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
            $table->string('kode_pesanan')->nullable()->unique()->after('id');
            $table->string('snap_token')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_dineins', function (Blueprint $table) {
            $table->dropColumn(['kode_pesanan', 'snap_token']);
        });
    }
};
