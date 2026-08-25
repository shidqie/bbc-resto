<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE item_paket MODIFY COLUMN tipe_item VARCHAR(50) NOT NULL");
            DB::statement("UPDATE item_paket SET tipe_item = 'wajib' WHERE tipe_item = 'tetap'");
        } else {
            DB::table('item_paket')->where('tipe_item', 'tetap')->update(['tipe_item' => 'wajib']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE item_paket SET tipe_item = 'tetap' WHERE tipe_item = 'wajib'");
            DB::statement("ALTER TABLE item_paket MODIFY COLUMN tipe_item ENUM('tetap', 'pilihan') NOT NULL");
        } else {
            DB::table('item_paket')->where('tipe_item', 'wajib')->update(['tipe_item' => 'tetap']);
        }
    }
};
