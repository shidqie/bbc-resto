<?php

namespace App\Models;

class StokCatering extends BaseModel
{
    protected $table = 'stok_catering';

    protected $guarded = [];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }
}
