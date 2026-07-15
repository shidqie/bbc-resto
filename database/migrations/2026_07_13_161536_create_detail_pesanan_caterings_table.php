<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_pesanan_caterings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_catering_id')->constrained('pesanan_caterings')->cascadeOnDelete();
            $table->foreignId('komponen_paket_id')->constrained('komponen_pakets');
            $table->foreignId('menu_id')->constrained('menus');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan_caterings');
    }
};
