<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuktiPembayaran extends Model
{
    protected $fillable = [
        'pesanan_id',
        'pesanan_type',
        'jenis_pembayaran',
        'file_path',
        'status',
        'catatan_admin'
    ];

    public function pesanan()
    {
        return $this->morphTo();
    }
}
