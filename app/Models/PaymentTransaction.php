<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    protected $table = 'payment_transactions';

    protected $guarded = [];

    protected $casts = [
        'raw_response' => 'array',
        'gross_amount' => 'integer',
    ];

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';
}
