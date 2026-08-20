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
        // First convert the enum column to a string column
        // We do this by dropping and recreating or using DB statement (easier in MySQL)
        
        DB::statement("ALTER TABLE item_paket MODIFY COLUMN tipe_item VARCHAR(50) NOT NULL");
        
        // Convert old values
        DB::statement("UPDATE item_paket SET tipe_item = 'wajib' WHERE tipe_item = 'tetap'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE item_paket SET tipe_item = 'tetap' WHERE tipe_item = 'wajib'");
        
        // Revert back to enum
        DB::statement("ALTER TABLE item_paket MODIFY COLUMN tipe_item ENUM('tetap', 'pilihan') NOT NULL");
    }
};
