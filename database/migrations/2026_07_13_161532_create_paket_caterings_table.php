<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_caterings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_paket');
            $table->enum('jenis_paket', ['catering', 'nasi_box']);
            $table->decimal('harga', 12, 0);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paket_caterings');
    }
};
