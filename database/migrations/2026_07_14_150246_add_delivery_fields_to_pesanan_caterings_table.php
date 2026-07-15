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
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->enum('metode_pengiriman', ['pickup', 'delivery'])->default('pickup')->after('status');
            $table->decimal('ongkos_kirim', 10, 2)->default(0)->after('total_tagihan');
            $table->decimal('jarak_km', 5, 2)->nullable()->after('lokasi_acara');
            $table->string('latitude')->nullable()->after('jarak_km');
            $table->string('longitude')->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_caterings', function (Blueprint $table) {
            $table->dropColumn(['metode_pengiriman', 'ongkos_kirim', 'jarak_km', 'latitude', 'longitude']);
        });
    }
};
