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
        Schema::create('item_pesanan_dineins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_dinein_id')->constrained('pesanan_dineins')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus');
            $table->integer('qty');
            $table->string('catatan')->nullable();
            $table->foreignId('diinput_oleh')->constrained('users');
            $table->timestamp('diinput_pada')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_pesanan_dineins');
    }
};
