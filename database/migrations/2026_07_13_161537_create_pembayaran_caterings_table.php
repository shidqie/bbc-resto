<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran_caterings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_catering_id')->constrained('pesanan_caterings')->onDelete('cascade');
            $table->enum('jenis_pembayaran', ['dp', 'pelunasan']);
            $table->decimal('jumlah_bayar', 12, 0);
            $table->enum('metode', ['cash', 'transfer', 'qris']);
            $table->string('bukti_bayar')->nullable(); // path file bukti transfer
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran_caterings');
    }
};
