<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_nasi_box_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_nasi_box_id')->constrained('pesanan_nasi_boxes')->cascadeOnDelete();
            $table->foreignId('komponen_paket_id')->constrained('komponen_pakets')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_nasi_box_details');
    }
};
