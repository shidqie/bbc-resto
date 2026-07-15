<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bukti_pembayarans', function (Blueprint $table) {
            $table->id();
            $table->morphs('pesanan'); // pesanan_id, pesanan_type
            $table->enum('jenis_pembayaran', ['dp', 'pelunasan']);
            $table->string('file_path');
            $table->enum('status', ['menunggu_verifikasi', 'verified', 'ditolak'])->default('menunggu_verifikasi');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bukti_pembayarans');
    }
};
