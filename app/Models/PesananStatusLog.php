<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PesananStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'pesanan_type',
        'pesanan_id',
        'status_lama',
        'status_baru',
        'user_id',
        'catatan',
    ];

    public function pesanan()
    {
        return $this->morphTo();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
