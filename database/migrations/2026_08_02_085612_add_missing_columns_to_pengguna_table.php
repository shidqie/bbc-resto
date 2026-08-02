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
        Schema::table('pengguna', function (Blueprint $table) {
            if (!Schema::hasColumn('pengguna', 'terakhir_masuk')) {
                $table->timestamp('terakhir_masuk')->nullable()->after('status_aktif');
            }
            if (!Schema::hasColumn('pengguna', 'status_aktif')) {
                $table->boolean('status_aktif')->default(true)->after('kata_sandi');
            }
            // Ensure nomor_telepon exists (it's in original migration as nomor_telepon)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengguna', function (Blueprint $table) {
            $table->dropColumn(['terakhir_masuk']);
        });
    }
};
