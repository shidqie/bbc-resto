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
        if (Schema::hasTable('detail_pesanan')) {
            Schema::table('detail_pesanan', function (Blueprint $table) {
                if (! Schema::hasColumn('detail_pesanan', 'is_tambahan')) {
                    $table->boolean('is_tambahan')->default(false)->after('catatan');
                }
                if (! Schema::hasColumn('detail_pesanan', 'batch_pesanan')) {
                    $table->unsignedTinyInteger('batch_pesanan')->default(1)->after('is_tambahan');
                }
                if (! Schema::hasColumn('detail_pesanan', 'waktu_dipesan')) {
                    $table->timestamp('waktu_dipesan')->nullable()->after('batch_pesanan');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('detail_pesanan')) {
            Schema::table('detail_pesanan', function (Blueprint $table) {
                if (Schema::hasColumn('detail_pesanan', 'waktu_dipesan')) {
                    $table->dropColumn('waktu_dipesan');
                }
                if (Schema::hasColumn('detail_pesanan', 'batch_pesanan')) {
                    $table->dropColumn('batch_pesanan');
                }
                if (Schema::hasColumn('detail_pesanan', 'is_tambahan')) {
                    $table->dropColumn('is_tambahan');
                }
            });
        }
    }
};
