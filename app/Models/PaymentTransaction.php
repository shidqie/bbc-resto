<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'din_number',
        'gross_amount',
        'payment_type',
        'transaction_status',
        'qr_url',
        'raw_response',
        'paid_at',
    ];

    protected $casts = [
        'raw_response' => 'array',
        'paid_at' => 'datetime',
        'gross_amount' => 'decimal:2',
    ];

    /**
     * Scope helper to check if payment is settled/paid
     */
    public function isPaid(): bool
    {
        return in_array($this->transaction_status, ['settlement', 'capture']);
    }
}
