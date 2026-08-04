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
        Schema::create('item_paket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menu')->cascadeOnDelete();
            $table->string('nama_item');
            $table->enum('tipe_item', ['tetap', 'pilihan'])->default('tetap');
            $table->integer('minimum_pilihan')->default(0);
            $table->integer('maksimum_pilihan')->default(0);
            $table->integer('urutan')->default(0);
            $table->timestamp('dibuat_pada') -> useCurrent();

            $table->timestamp('diperbarui_pada') -> useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('pilihan_item_paket', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_paket_id')->constrained('item_paket')->cascadeOnDelete();
            $table->string('nama_pilihan');
            $table->integer('urutan')->default(0);
            $table->timestamp('dibuat_pada') -> useCurrent();

            $table->timestamp('diperbarui_pada') -> useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('pilihan_pesanan_catering', function (Blueprint $table) {
            $table->id();
            $table->foreignId('detail_pesanan_id')->constrained('detail_pesanan')->cascadeOnDelete();
            $table->foreignId('item_paket_id')->constrained('item_paket')->cascadeOnDelete();
            $table->foreignId('pilihan_item_paket_id')->constrained('pilihan_item_paket')->cascadeOnDelete();
            $table->timestamp('dibuat_pada') -> useCurrent();

            $table->timestamp('diperbarui_pada') -> useCurrent()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilihan_pesanan_catering');
        Schema::dropIfExists('pilihan_item_paket');
        Schema::dropIfExists('item_paket');
    }
};
