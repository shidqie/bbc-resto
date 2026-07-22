<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_kasirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('modal_awal', 15, 2)->default(0);
            $table->enum('status', ['buka', 'tutup'])->default('buka');
            $table->timestamp('dibuka_pada')->useCurrent();
            $table->timestamp('ditutup_pada')->nullable();
            $table->decimal('total_penjualan_tunai', 15, 2)->default(0);
            $table->decimal('total_penjualan_qris', 15, 2)->default(0);
            $table->decimal('kas_akhir', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_kasirs');
    }
};
