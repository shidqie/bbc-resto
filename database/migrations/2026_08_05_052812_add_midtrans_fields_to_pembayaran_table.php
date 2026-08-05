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
            $table->string('midtrans_order_id')->nullable()->after('status_pembayaran_id');
            $table->string('midtrans_transaction_id')->nullable()->after('midtrans_order_id');
            $table->text('qr_code_url')->nullable()->after('midtrans_transaction_id');
            $table->dateTime('expired_at')->nullable()->after('qr_code_url');
            $table->json('response_midtrans')->nullable()->after('expired_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn([
                'midtrans_order_id',
                'midtrans_transaction_id',
                'qr_code_url',
                'expired_at',
                'response_midtrans'
            ]);
        });
    }
};
