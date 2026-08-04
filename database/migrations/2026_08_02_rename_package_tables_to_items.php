<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Rename the tables to new names
        if (Schema::hasTable('komponen_paket')) {
            Schema::rename('komponen_paket', 'item_paket');
        }
        
        if (Schema::hasTable('pilihan_komponen_paket')) {
            Schema::rename('pilihan_komponen_paket', 'pilihan_item_paket');
        }
    }

    public function down(): void
    {
        // Rename back for rollback
        if (Schema::hasTable('item_paket')) {
            Schema::rename('item_paket', 'komponen_paket');
        }
        
        if (Schema::hasTable('pilihan_item_paket')) {
            Schema::rename('pilihan_item_paket', 'pilihan_komponen_paket');
        }
    }
};