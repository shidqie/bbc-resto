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
        Schema::table('menus', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('harga');
            $table->enum('jenis_menu', ['dine_in', 'catering', 'nasi_box'])->default('dine_in')->after('nama');
            $table->text('deskripsi')->nullable()->after('jenis_menu');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['foto', 'jenis_menu', 'deskripsi']);
        });
    }
};
