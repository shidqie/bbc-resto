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
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->time('waktu_acara')->nullable()->after('tanggal_acara');
        });

        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->time('waktu_acara')->nullable()->after('tanggal_acara');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_tables', function (Blueprint $table) {
            //
        });
    }
};
