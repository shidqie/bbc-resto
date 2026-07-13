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
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_status', ['lunas', 'dp'])->default('lunas')->after('payment_method');
            $table->decimal('dp_amount', 12, 2)->nullable()->after('payment_status');
            $table->decimal('remaining_amount', 12, 2)->nullable()->after('dp_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'dp_amount', 'remaining_amount']);
        });
    }
};
