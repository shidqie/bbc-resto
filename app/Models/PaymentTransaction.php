<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PaymentTransaction Model
 * 
 * Tracks Midtrans payment transactions with comprehensive logging and audit trail.
 * Enhanced for payment system fix specification requirements 6.4 and 8.5.
 * 
 * @property string $order_id Unique order identifier for Midtrans
 * @property string $din_number DIN/nomor pesanan for order reference  
 * @property int $gross_amount Payment amount in smallest currency unit
 * @property string $payment_type Payment method (qris, va, etc.)
 * @property string $transaction_status Current payment status
 * @property array $raw_response Full Midtrans API response
 * @property bool $signature_verified Whether webhook signature was verified
 * @property \Carbon\Carbon $processed_at When payment was processed
 * @property \Carbon\Carbon $webhook_received_at When webhook was received
 * @property int $retry_count Number of retry attempts for failed processing
 */
class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $fillable = [
        'order_id',
        'din_number',
        'gross_amount',
        'payment_type',
        'transaction_status',
        'qr_url',
        'raw_response',
        'signature_verified',
        'processed_at',
        'webhook_received_at',
        'retry_count'
    ];

    protected $casts = [
        'raw_response' => 'array',
        'gross_amount' => 'integer',
        'signature_verified' => 'boolean',
        'processed_at' => 'datetime',
        'webhook_received_at' => 'datetime',
        'retry_count' => 'integer'
    ];

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';
}
