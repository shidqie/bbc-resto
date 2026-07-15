<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_nasi_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan')->unique();
            $table->string('nama_pemesan');
            $table->string('kontak');
            $table->text('alamat');
            $table->date('tanggal_acara');
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->integer('jumlah_box');
            $table->decimal('total_tagihan', 12, 0);
            $table->decimal('dp_amount', 12, 0);
            $table->enum('status', ['menunggu_dp', 'menunggu_konfirmasi', 'terkonfirmasi', 'lunas', 'dibatalkan'])->default('menunggu_dp');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_nasi_boxes');
    }
};
