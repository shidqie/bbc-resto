<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_caterings', function (Blueprint $table) {
            $table->id();
            $table->string('no_pesanan')->unique();
            $table->foreignId('paket_catering_id')->constrained('paket_caterings');
            $table->string('nama_pemesan');
            $table->string('no_telepon');
            $table->string('email')->nullable();
            $table->text('alamat_pengiriman');
            $table->date('tanggal_acara');
            $table->text('detail_acara')->nullable();
            $table->integer('jumlah_porsi');
            $table->decimal('harga_per_porsi', 12, 0);
            $table->decimal('total_harga', 12, 0);
            $table->decimal('dp_amount', 12, 0)->default(0);
            $table->decimal('dp_percentage', 5, 2)->default(0);
            $table->decimal('sisa_pembayaran', 12, 0)->default(0);
            $table->enum('status', ['menunggu_konfirmasi', 'terkonfirmasi', 'lunas', 'dibatalkan', 'selesai'])->default('menunggu_konfirmasi');
            $table->foreignId('confirmed_by')->nullable()->constrained('users');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('catatan_pembatalan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_caterings');
    }
};
