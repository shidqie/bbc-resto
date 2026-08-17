<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order', function (Blueprint $table) {
            // Make pengadaan_bahan_id nullable
            $table->unsignedBigInteger('pengadaan_bahan_id')->nullable()->change();
            
            // Add supplier details
            $table->string('no_telp_supplier', 30)->nullable()->after('supplier');
            $table->text('alamat_supplier')->nullable()->after('no_telp_supplier');
            
            // Add jenis_po (operasional or catering)
            $table->string('jenis_po', 20)->default('operasional')->after('status');
            $table->string('kode_pesanan_catering', 50)->nullable()->after('jenis_po');
        });

        Schema::table('detail_purchase_order', function (Blueprint $table) {
            // Make detail_pengadaan_bahan_id nullable
            $table->unsignedBigInteger('detail_pengadaan_bahan_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('detail_purchase_order', function (Blueprint $table) {
            $table->unsignedBigInteger('detail_pengadaan_bahan_id')->nullable(false)->change();
        });

        Schema::table('purchase_order', function (Blueprint $table) {
            $table->unsignedBigInteger('pengadaan_bahan_id')->nullable(false)->change();
            $table->dropColumn('no_telp_supplier');
            $table->dropColumn('alamat_supplier');
            $table->dropColumn('jenis_po');
            $table->dropColumn('kode_pesanan_catering');
        });
    }
};
