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
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Add signature verification tracking fields
            $table->boolean('signature_verified')->default(false)->after('raw_response');
            
            // Add timestamp fields for comprehensive logging
            $table->timestamp('processed_at')->nullable()->after('signature_verified');
            $table->timestamp('webhook_received_at')->nullable()->after('processed_at');
            
            // Add retry count for failed webhook processing
            $table->integer('retry_count')->default(0)->after('webhook_received_at');
            
            // Add proper indexing for performance
            $table->index('din_number', 'idx_din_number');
            $table->index('transaction_status', 'idx_transaction_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex('idx_din_number');
            $table->dropIndex('idx_transaction_status');
            
            // Drop added columns
            $table->dropColumn([
                'signature_verified',
                'processed_at',
                'webhook_received_at',
                'retry_count'
            ]);
        });
    }
};
