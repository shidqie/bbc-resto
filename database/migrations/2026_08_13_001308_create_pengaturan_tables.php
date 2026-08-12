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
        Schema::create('pengaturan_transaksi', function (Blueprint $table) {
            $table->id();
            $table->boolean('pajak_aktif')->default(true);
            $table->decimal('persentase_pajak', 5, 2)->default(10.00);
            $table->boolean('layanan_aktif')->default(true);
            $table->decimal('persentase_layanan', 5, 2)->default(5.00);
            $table->foreignId('diperbarui_oleh')->nullable()->constrained('pengguna');
            $table->timestamps();
        });

        Schema::create('riwayat_pengaturan_transaksi', function (Blueprint $table) {
            $table->id();
            $table->json('nilai_lama');
            $table->json('nilai_baru');
            $table->foreignId('diubah_oleh')->constrained('pengguna');
            $table->timestamp('dibuat_pada')->useCurrent();
        });

        Schema::create('pengaturan_pengiriman', function (Blueprint $table) {
            $table->id();
            $table->decimal('tarif_per_km', 15, 2)->default(5000);
            $table->boolean('status_aktif')->default(true);
            $table->foreignId('diperbarui_oleh')->nullable()->constrained('pengguna');
            $table->timestamps();
        });

        Schema::create('aturan_pengiriman', function (Blueprint $table) {
            $table->id();
            $table->integer('minimal_porsi');
            $table->integer('maksimal_porsi')->nullable();
            $table->decimal('kilometer_gratis', 8, 2)->default(0);
            $table->boolean('status_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('riwayat_pengaturan_pengiriman', function (Blueprint $table) {
            $table->id();
            $table->json('nilai_lama');
            $table->json('nilai_baru');
            $table->foreignId('diubah_oleh')->constrained('pengguna');
            $table->timestamp('dibuat_pada')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_pengaturan_pengiriman');
        Schema::dropIfExists('aturan_pengiriman');
        Schema::dropIfExists('pengaturan_pengiriman');
        Schema::dropIfExists('riwayat_pengaturan_transaksi');
        Schema::dropIfExists('pengaturan_transaksi');
    }
};
