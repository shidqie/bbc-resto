<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detail_pengadaan_bahan', function (Blueprint $table) {
            $table->decimal('stok_saat_ini', 15, 3)->nullable()->after('jumlah_dipesan');
            $table->decimal('stok_minimum', 15, 3)->nullable()->after('stok_saat_ini');
        });

        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->string('kode_permintaan', 30)->nullable()->after('pengadaan_bahan_id');
            $table->string('supplier', 150)->nullable()->after('nomor_nota');
            $table->string('status', 30)->default('menunggu_penerimaan')->after('supplier');
            $table->foreignId('diverifikasi_oleh')->nullable()->after('diterima_oleh')->constrained('pengguna');
            $table->timestamp('waktu_verifikasi')->nullable()->after('diverifikasi_oleh');
        });

        Schema::table('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->foreignId('bahan_baku_id')->nullable()->after('detail_pengadaan_bahan_id')->constrained('bahan_baku');
            $table->decimal('jumlah_diminta', 15, 3)->nullable()->after('jumlah_diterima');
            $table->decimal('jumlah_kurang', 15, 3)->nullable()->after('jumlah_diminta');
            $table->foreignId('satuan_id')->nullable()->after('jumlah_kurang')->constrained('satuan');
            $table->enum('kondisi', ['Baik', 'Rusak', 'Kurang'])->nullable()->default('Baik')->after('satuan_id');
        });

        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->dropColumn('perkiraan_tanggal_datang');
        });
    }

    public function down(): void
    {
        Schema::table('pengadaan_bahan', function (Blueprint $table) {
            $table->date('perkiraan_tanggal_datang')->nullable();
        });

        Schema::table('detail_penerimaan_bahan', function (Blueprint $table) {
            $table->dropForeign(['bahan_baku_id']);
            $table->dropForeign(['satuan_id']);
            $table->dropColumn(['bahan_baku_id', 'jumlah_diminta', 'jumlah_kurang', 'satuan_id', 'kondisi']);
        });

        Schema::table('penerimaan_bahan', function (Blueprint $table) {
            $table->dropForeign(['diverifikasi_oleh']);
            $table->dropColumn(['kode_permintaan', 'supplier', 'status', 'diverifikasi_oleh', 'waktu_verifikasi']);
        });

        Schema::table('detail_pengadaan_bahan', function (Blueprint $table) {
            $table->dropColumn(['stok_saat_ini', 'stok_minimum']);
        });
    }
};
