<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Selaraskan tabel tiket_dapur (KOT) dengan model & alur produksi.
 * Kolom berikut dipakai oleh app/Services/DineInService, app/Models/TiketDapur,
 * serta tampilan cetak KOT, tetapi belum pernah ditambahkan melalui migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tiket_dapur', function (Blueprint $table) {
            if (! Schema::hasColumn('tiket_dapur', 'meja_id')) {
                $table->foreignId('meja_id')->nullable()->after('pesanan_id')->constrained('meja')->nullOnDelete();
            }
            if (! Schema::hasColumn('tiket_dapur', 'nomor_meja')) {
                $table->string('nomor_meja', 50)->nullable()->after('meja_id');
            }
            if (! Schema::hasColumn('tiket_dapur', 'nama_konsumen')) {
                $table->string('nama_konsumen', 150)->nullable()->after('nomor_meja');
            }
            if (! Schema::hasColumn('tiket_dapur', 'jumlah_tamu')) {
                $table->integer('jumlah_tamu')->default(1)->after('nama_konsumen');
            }
            if (! Schema::hasColumn('tiket_dapur', 'sumber_pesanan')) {
                $table->string('sumber_pesanan', 50)->nullable()->after('jumlah_tamu');
            }
            if (! Schema::hasColumn('tiket_dapur', 'siap_pada')) {
                $table->dateTime('siap_pada')->nullable()->after('diproses_pada');
            }
            if (! Schema::hasColumn('tiket_dapur', 'selesai_pada')) {
                $table->dateTime('selesai_pada')->nullable()->after('siap_pada');
            }
            if (! Schema::hasColumn('tiket_dapur', 'diproses_oleh')) {
                $table->unsignedBigInteger('diproses_oleh')->nullable()->after('siap_pada');
                $table->foreign('diproses_oleh')->references('id')->on('pengguna')->nullOnDelete();
            }
            if (! Schema::hasColumn('tiket_dapur', 'diselesaikan_oleh')) {
                $table->unsignedBigInteger('diselesaikan_oleh')->nullable()->after('selesai_pada');
                $table->foreign('diselesaikan_oleh')->references('id')->on('pengguna')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tiket_dapur', function (Blueprint $table) {
            if (Schema::hasColumn('tiket_dapur', 'diselesaikan_oleh')) {
                $table->dropForeign(['diselesaikan_oleh']);
                $table->dropColumn('diselesaikan_oleh');
            }
            if (Schema::hasColumn('tiket_dapur', 'diproses_oleh')) {
                $table->dropForeign(['diproses_oleh']);
                $table->dropColumn('diproses_oleh');
            }
            foreach (['selesai_pada', 'siap_pada', 'sumber_pesanan', 'jumlah_tamu', 'nama_konsumen', 'nomor_meja', 'meja_id'] as $col) {
                if (Schema::hasColumn('tiket_dapur', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
