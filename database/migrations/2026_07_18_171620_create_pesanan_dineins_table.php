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
        Schema::create('pesanan_dineins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meja_id')->constrained('mejas')->onDelete('cascade');
            $table->enum('status', ['menunggu_pembayaran', 'lunas', 'selesai'])->default('menunggu_pembayaran');
            $table->foreignId('dibuka_oleh')->constrained('users');
            $table->timestamp('dibuka_pada')->useCurrent();
            $table->timestamp('dibayar_pada')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_dineins');
    }
};
