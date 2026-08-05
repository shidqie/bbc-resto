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
        Schema::table('pembayaran', function (Blueprint $table) {
            // Upload progress tracking (0-100)
            $table->tinyInteger('upload_progress')->default(0)->after('bukti_pembayaran');
            
            // File hash for verification and duplicate detection
            $table->string('file_hash', 64)->nullable()->after('upload_progress');
            
            // Admin verification notes
            $table->text('verification_notes')->nullable()->after('file_hash');
            
            // Auto verification flag for automatic payment confirmations
            $table->boolean('auto_verified')->default(false)->after('verification_notes');
            
            // Store webhook data from payment gateways (JSON)
            $table->json('webhook_data')->nullable()->after('auto_verified');
            
            // Payment method details (e.g., VA number, QRIS details)
            $table->json('payment_method_details')->nullable()->after('webhook_data');
            
            // Add indexes for performance
            $table->index(['auto_verified', 'status_pembayaran_id']);
            $table->index('file_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropIndex(['auto_verified', 'status_pembayaran_id']);
            $table->dropIndex(['file_hash']);
            $table->dropColumn([
                'upload_progress',
                'file_hash', 
                'verification_notes',
                'auto_verified',
                'webhook_data',
                'payment_method_details'
            ]);
        });
    }
};
