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
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('service_type');
            $table->boolean('is_dine_in')->default(true)->after('category_id');
            $table->boolean('is_catering')->default(false)->after('is_dine_in');
            $table->boolean('is_nasi_box')->default(false)->after('is_catering');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_dine_in', 'is_catering', 'is_nasi_box']);
            $table->string('service_type', 20)->default('semua')->after('category_id');
        });
    }
};
