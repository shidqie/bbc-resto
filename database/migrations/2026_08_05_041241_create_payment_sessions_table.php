<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates payment_sessions table for managing payment token expiration and session security.
     * Addresses requirements 4.5-4.6 for countdown timers and payment expiration, 
     * and requirement 7.6 for session security.
     */
    public function up(): void
    {
        Schema::create('payment_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_token', 255)->unique()->index();
            $table->foreignId('pesanan_id')->constrained('pesanan')->onDelete('cascade');
            $table->enum('payment_type', ['dp', 'pelunasan'])->index();
            $table->decimal('amount', 15, 2);
            $table->timestamp('expires_at')->index();
            $table->enum('status', ['active', 'completed', 'expired', 'cancelled'])->default('active')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_sessions');
    }
};
