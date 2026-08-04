<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan kolom status_item pada detail_pesanan untuk pelacakan status sajian
 * per item (KOT): null = belum disajikan, 'disajikan' = sudah disajikan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_pesanan', 'status_item')) {
                $table->string('status_item', 20)->nullable()->after('catatan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('detail_pesanan', 'status_item')) {
                $table->dropColumn('status_item');
            }
        });
    }
};
