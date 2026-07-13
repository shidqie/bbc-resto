<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $fillable = [
        'pesanan_id',
        'jumlah_bayar',
        'metode_pembayaran',
        'jenis_pembayaran',
        'tanggal_bayar',
        'referensi'
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime'
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class);
    }
}
