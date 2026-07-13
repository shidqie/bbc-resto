<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'invoice_number',
        'customer_name',
        'table_number',
        'event_date',
        'service_type',
        'total_amount',
        'payment_method',
        'payment_status',
        'dp_amount',
        'remaining_amount',
        'cash_received',
        'change_amount',
        'note',
        'user_id'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
