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
        Schema::create('komponen_paket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
            $table->string('nama_komponen');
            $table->enum('tipe_komponen', ['tetap', 'pilihan'])->default('tetap');
            $table->integer('minimum_pilihan')->default(0);
            $table->integer('maksimum_pilihan')->default(0);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('pilihan_komponen_paket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('komponen_paket_id')->constrained('komponen_paket')->cascadeOnDelete();
            $table->string('nama_pilihan');
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        Schema::create('pilihan_pesanan_catering', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_pesanan_id')->constrained('detail_pesanan')->cascadeOnDelete();
            $table->foreignId('komponen_paket_id')->constrained('komponen_paket')->cascadeOnDelete();
            $table->foreignId('pilihan_komponen_paket_id')->constrained('pilihan_komponen_paket')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilihan_pesanan_catering');
        Schema::dropIfExists('pilihan_komponen_paket');
        Schema::dropIfExists('komponen_paket');
    }
};
