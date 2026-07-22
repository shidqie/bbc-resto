<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengadaans', function (Blueprint $table) {
            if (!Schema::hasColumn('pengadaans', 'pesanan_nasi_box_id')) {
                $table->foreignId('pesanan_nasi_box_id')->nullable()->after('pesanan_catering_id')->constrained('pesanan_nasi_boxes')->nullOnDelete();
            }
            if (!Schema::hasColumn('pengadaans', 'jenis_pesanan')) {
                $table->enum('jenis_pesanan', ['catering', 'nasi_box', 'umum'])->default('catering')->after('pesanan_nasi_box_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengadaans', function (Blueprint $table) {
            $table->dropForeign(['pesanan_nasi_box_id']);
            $table->dropColumn(['pesanan_nasi_box_id', 'jenis_pesanan']);
        });
    }
};
