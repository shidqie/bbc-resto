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
        Schema::table('meja', function (Blueprint $table) {
            $table->string('kode_meja')->unique()->nullable()->after('id');
            $table->string('qr_token', 64)->unique()->nullable()->after('kapasitas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meja', function (Blueprint $table) {
            $table->dropColumn(['kode_meja', 'qr_token']);
        });
    }
};
