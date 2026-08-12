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
        Schema::table('pesanan', function (Blueprint $table) {
            $table->decimal('persentase_pajak', 5, 2)->default(0)->after('jumlah_pajak');
            $table->decimal('persentase_biaya_layanan', 5, 2)->default(0)->after('biaya_pelayanan');
        });

        Schema::table('pengantaran', function (Blueprint $table) {
            $table->decimal('tarif_per_km', 15, 2)->default(0)->after('biaya_pengantaran');
            $table->decimal('jarak_gratis', 8, 2)->default(0)->after('tarif_per_km');
            $table->decimal('jarak_berbayar', 8, 2)->default(0)->after('jarak_gratis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengantaran', function (Blueprint $table) {
            $table->dropColumn(['tarif_per_km', 'jarak_gratis', 'jarak_berbayar']);
        });

        Schema::table('pesanan', function (Blueprint $table) {
            $table->dropColumn(['persentase_pajak', 'persentase_biaya_layanan']);
        });
    }
};
