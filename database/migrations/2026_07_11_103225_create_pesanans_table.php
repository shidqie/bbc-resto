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
        Schema::create('pesanans', function (Blueprint $table) {
            $table->id();
            $table->string('no_pesanan')->unique();
            $table->string('nama_pelanggan')->nullable();
            $table->string('no_meja')->nullable();
            $table->enum('jenis_pesanan', ['dine_in', 'take_away', 'catering', 'nasi_box']);
            $table->datetime('tanggal_pesanan');
            $table->datetime('tanggal_pengiriman')->nullable(); // For catering/nasi box
            $table->integer('jumlah_porsi')->default(1);
            $table->decimal('total_harga', 12, 2)->default(0);
            $table->enum('status_pembayaran', ['belum_bayar', 'dp', 'lunas', 'refund'])->default('belum_bayar');
            $table->enum('status_pesanan', ['baru', 'diproses', 'selesai', 'dibatalkan', 'dikirim'])->default('baru');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Kasir/Admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanans');
    }
};
