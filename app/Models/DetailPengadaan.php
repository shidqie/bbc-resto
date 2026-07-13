<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPengadaan extends Model
{
    protected $fillable = [
        'pengadaan_id',
        'bahan_baku_id',
        'jumlah',
        'satuan',
        'harga_satuan',
        'subtotal'
    ];

    public function pengadaan()
    {
        return $this->belongsTo(Pengadaan::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
