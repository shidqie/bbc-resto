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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('customer_name')->nullable();
            $table->string('table_number')->nullable();
            $table->string('service_type', 20)->default('dine_in')->comment('dine_in, catering, nasi_box');
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_method', 20)->nullable()->comment('cash, qris, transfer');
            $table->decimal('cash_received', 12, 2)->nullable();
            $table->decimal('change_amount', 12, 2)->nullable();
            $table->text('note')->nullable();
            $table->foreignId('user_id')->constrained()->comment('kasir yang memproses');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
