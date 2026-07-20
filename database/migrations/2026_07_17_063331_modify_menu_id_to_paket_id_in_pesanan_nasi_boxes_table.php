<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->dropForeign(['menu_id']);
            $table->dropColumn('menu_id');
        });
        
        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->foreignId('paket_id')->after('tanggal_acara')->constrained('paket_caterings')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->dropForeign(['paket_id']);
            $table->dropColumn('paket_id');
        });
        
        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->foreignId('menu_id')->after('tanggal_acara')->constrained('menus')->cascadeOnDelete();
        });
    }
};
