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
        Schema::table('pengadaans', function (Blueprint $table) {
            $table->foreignId('pesanan_catering_id')->nullable()->after('supplier_id')->constrained('pesanan_caterings')->nullOnDelete();
        });

        Schema::table('detail_pengadaans', function (Blueprint $table) {
            // Rename existing columns to estimasi (requires doctrine/dbal if using rename, but we can just use eloquent or raw queries if needed. Actually in Laravel 9+ renameColumn is supported without DBAL for many drivers, but let's just add new columns to be safe and avoid dropping data or needing DBAL)
            
            // Kolom estimasi
            $table->decimal('jumlah_estimasi', 10, 2)->nullable()->after('jumlah');
            $table->decimal('harga_estimasi', 15, 2)->nullable()->after('harga_satuan');
            $table->decimal('subtotal_estimasi', 15, 2)->nullable()->after('subtotal');
            
            // Kolom realisasi
            $table->decimal('jumlah_real', 10, 2)->nullable()->after('subtotal_estimasi');
            $table->decimal('harga_real', 15, 2)->nullable()->after('jumlah_real');
            $table->decimal('subtotal_real', 15, 2)->nullable()->after('harga_real');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detail_pengadaans', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_estimasi', 'harga_estimasi', 'subtotal_estimasi',
                'jumlah_real', 'harga_real', 'subtotal_real'
            ]);
        });

        Schema::table('pengadaans', function (Blueprint $table) {
            $table->dropForeign(['pesanan_catering_id']);
            $table->dropColumn('pesanan_catering_id');
        });
    }
};
