<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            if (!Schema::hasColumn('bahan_bakus', 'jenis_penggunaan')) {
                $table->enum('jenis_penggunaan', ['dine_in', 'catering', 'nasi_box', 'semua'])->default('semua')->after('nama_bahan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bahan_bakus', function (Blueprint $table) {
            $table->dropColumn('jenis_penggunaan');
        });
    }
};
