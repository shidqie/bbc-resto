<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftKasir extends Model
{
    protected $table = 'shift_kasirs';

    protected $fillable = [
        'user_id',
        'modal_awal',
        'status',
        'dibuka_pada',
        'ditutup_pada',
        'total_penjualan_tunai',
        'total_penjualan_qris',
        'kas_akhir',
    ];

    protected $casts = [
        'dibuka_pada' => 'datetime',
        'ditutup_pada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
