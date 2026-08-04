<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifikasi_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')->constrained('bahan_baku')->cascadeOnDelete();
            $table->string('jenis', 20)->default('menipis');
            $table->decimal('stok_saat_ini', 10, 3)->default(0);
            $table->decimal('stok_minimal', 10, 3)->default(0);
            $table->text('pesan')->nullable();
            $table->boolean('dibaca')->default(false);
            $table->timestamp('dibaca_pada')->nullable();
            $table->unsignedBigInteger('dibaca_oleh')->nullable();
            $table->timestamp('dibuat_pada')->useCurrent();
            $table->timestamp('diperbarui_pada')->useCurrent()->useCurrentOnUpdate();

            $table->index('jenis');
            $table->index('dibaca');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifikasi_stoks');
    }
};
