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
        Schema::create('pembayaran_dineins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_dinein_id')->constrained('pesanan_dineins')->onDelete('cascade');
            $table->enum('metode_bayar', ['cash', 'qris', 'kartu']);
            $table->decimal('total', 15, 2);
            $table->foreignId('diproses_oleh')->constrained('users');
            $table->timestamp('diproses_pada')->useCurrent();
            $table->enum('status', ['lunas', 'void', 'refund'])->default('lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran_dineins');
    }
};
