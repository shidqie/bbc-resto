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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 100)->unique();
            $table->string('din_number', 100)->nullable(); // DIN/nomor pesanan
            $table->unsignedBigInteger('gross_amount')->default(0);
            $table->string('payment_type', 50)->default('qris');
            $table->string('transaction_status', 50)->default('pending');
            $table->text('qr_url')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
