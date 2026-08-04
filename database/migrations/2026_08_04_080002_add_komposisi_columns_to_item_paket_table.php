<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perluas item_paket & pilihan_item_paket agar bisa memuat:
     *  - menu_id_terkait : menu satuan yang ditautkan (untuk item tetap)
     *  - jumlah          : jumlah takaran/unit
     *  - satuan_sajian   : satuan sajian (porsi, bungkus, cup, botol, dll)
     */
    public function up(): void
    {
        Schema::table('item_paket', function (Blueprint $table) {
            $table->unsignedBigInteger('menu_id_terkait')->nullable()->after('menu_id');
            $table->foreign('menu_id_terkait')->references('id')->on('menu')->nullOnDelete();
            $table->decimal('jumlah', 10, 2)->default(1)->after('menu_id_terkait');
            $table->string('satuan_sajian', 50)->nullable()->after('jumlah');
        });

        Schema::table('pilihan_item_paket', function (Blueprint $table) {
            $table->decimal('jumlah', 10, 2)->default(1)->after('menu_id');
            $table->string('satuan_sajian', 50)->nullable()->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('item_paket', function (Blueprint $table) {
            $table->dropForeign(['menu_id_terkait']);
            $table->dropColumn(['menu_id_terkait', 'jumlah', 'satuan_sajian']);
        });

        Schema::table('pilihan_item_paket', function (Blueprint $table) {
            $table->dropColumn(['jumlah', 'satuan_sajian']);
        });
    }
};
