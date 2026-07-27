<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranDinein extends Model
{
    protected $fillable = [
        'pesanan_dinein_id',
        'metode_bayar',
        'total',
        'diproses_oleh',
        'diproses_pada',
        'status',
    ];
    public function diprosesOleh()
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
