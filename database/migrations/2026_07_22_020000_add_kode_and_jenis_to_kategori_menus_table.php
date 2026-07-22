<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kategori_menus', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori_menus', 'kode_kategori')) {
                $table->string('kode_kategori')->nullable()->after('id');
            }
            if (!Schema::hasColumn('kategori_menus', 'jenis_menu')) {
                $table->enum('jenis_menu', ['dine_in', 'catering', 'nasi_box'])->default('dine_in')->after('nama');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kategori_menus', function (Blueprint $table) {
            $table->dropColumn(['kode_kategori', 'jenis_menu']);
        });
    }
};
