<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom keterangan & dikonfirmasi pada resep_menu,
     * serta unique(menu_id, bahan_baku_id) agar bahan tidak dobel.
     */
    public function up(): void
    {
        Schema::table('resep_menu', function (Blueprint $table) {
            $table->string('keterangan', 255)->nullable()->after('satuan_id');
            $table->boolean('dikonfirmasi')->default(false)->after('keterangan');
        });

        // Hapus duplikat (jika ada) sebelum pasang unique index.
        // MySQL & SQLite (test) memiliki sintaks DELETE yang berbeda.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('DELETE r1 FROM resep_menu r1 INNER JOIN resep_menu r2 WHERE r1.id > r2.id AND r1.menu_id = r2.menu_id AND r1.bahan_baku_id = r2.bahan_baku_id');
        } else {
            DB::statement('DELETE FROM resep_menu WHERE id IN (SELECT r1.id FROM resep_menu r1 INNER JOIN resep_menu r2 WHERE r1.id > r2.id AND r1.menu_id = r2.menu_id AND r1.bahan_baku_id = r2.bahan_baku_id)');
        }

        Schema::table('resep_menu', function (Blueprint $table) {
            $table->unique(['menu_id', 'bahan_baku_id'], 'resep_menu_menu_bahan_unique');
        });
    }

    public function down(): void
    {
        Schema::table('resep_menu', function (Blueprint $table) {
            $table->dropUnique('resep_menu_menu_bahan_unique');
            $table->dropColumn('dikonfirmasi');
            $table->dropColumn('keterangan');
        });
    }
};
