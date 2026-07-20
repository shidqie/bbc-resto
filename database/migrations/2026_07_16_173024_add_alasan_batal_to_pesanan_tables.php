<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->text('alasan_batal')->nullable()->after('catatan');
        });

        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->text('alasan_batal')->nullable()->after('catatan');
        });
    }

    public function down(): void
    {
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->dropColumn('alasan_batal');
        });

        Schema::table('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->dropColumn('alasan_batal');
        });
    }
};
