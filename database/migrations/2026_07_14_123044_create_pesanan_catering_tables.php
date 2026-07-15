<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old tables to recreate according to new PRD
        Schema::dropIfExists('pembayaran_caterings');
        Schema::dropIfExists('detail_pesanan_caterings');
        Schema::dropIfExists('pesanan_caterings');

        Schema::create('pesanan_caterings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan')->unique();
            $table->string('nama_pemesan');
            $table->string('kontak');
            $table->text('lokasi_acara');
            $table->date('tanggal_acara');
            $table->foreignId('paket_id')->constrained('paket_caterings')->cascadeOnDelete();
            $table->integer('jumlah_porsi');
            $table->decimal('total_tagihan', 12, 0);
            $table->decimal('dp_amount', 12, 0);
            $table->enum('status', ['menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'lunas', 'dibatalkan'])->default('menunggu_dp');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('pesanan_catering_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan_caterings')->cascadeOnDelete();
            $table->foreignId('komponen_id')->constrained('komponen_pakets')->cascadeOnDelete();
            $table->foreignId('menu_id_terpilih')->constrained('menus')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('pesanan_catering_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->constrained('pesanan_caterings')->cascadeOnDelete();
            $table->foreignId('layanan_tambahan_id')->constrained('layanan_tambahans')->cascadeOnDelete();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_catering_addons');
        Schema::dropIfExists('pesanan_catering_details');
        Schema::dropIfExists('pesanan_caterings');
    }
};
