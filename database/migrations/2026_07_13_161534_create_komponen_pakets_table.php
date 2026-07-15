<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('komponen_pakets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paket_catering_id')->constrained('paket_caterings')->cascadeOnDelete();
            $table->string('nama_komponen');
            $table->enum('tipe', ['fixed', 'choice']);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_pakets');
    }
};
