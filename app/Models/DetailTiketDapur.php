<?php

namespace App\Models;

class DetailTiketDapur extends BaseModel
{
    protected $table = 'detail_tiket_dapur';

    protected $guarded = [];

    public $timestamps = false;

    public function tiket_dapur()
    {
        return $this->belongsTo(TiketDapur::class, 'tiket_dapur_id');
    }

    public function detail_pesanan()
    {
        return $this->belongsTo(DetailPesanan::class, 'detail_pesanan_id');
    }
}
