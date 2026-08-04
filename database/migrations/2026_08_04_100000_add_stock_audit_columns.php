<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * P0 — Audit stok (FR-10, FR-11):
 * - detail_pesanan.stock_deducted_at: penanda idempotent agar stok tidak dipotong ganda.
 * - mutasi_stok.stok_sebelum / stok_sesudah: saldo sebelum & sesudah untuk kartu stok.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (! Schema::hasColumn('detail_pesanan', 'stock_deducted_at')) {
                $table->dateTime('stock_deducted_at')->nullable()->after('status_item');
            }
        });

        Schema::table('mutasi_stok', function (Blueprint $table) {
            if (! Schema::hasColumn('mutasi_stok', 'stok_sebelum')) {
                $table->decimal('stok_sebelum', 15, 3)->nullable()->after('jumlah');
            }
            if (! Schema::hasColumn('mutasi_stok', 'stok_sesudah')) {
                $table->decimal('stok_sesudah', 15, 3)->nullable()->after('stok_sebelum');
            }
        });
    }

    public function down(): void
    {
        Schema::table('detail_pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('detail_pesanan', 'stock_deducted_at')) {
                $table->dropColumn('stock_deducted_at');
            }
        });

        Schema::table('mutasi_stok', function (Blueprint $table) {
            foreach (['stok_sebelum', 'stok_sesudah'] as $col) {
                if (Schema::hasColumn('mutasi_stok', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
