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
        Schema::table('pilihan_item_paket', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('nama_pilihan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pilihan_item_paket', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
