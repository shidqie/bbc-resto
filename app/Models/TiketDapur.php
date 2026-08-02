<?php

namespace App\Models;

class TiketDapur extends BaseModel
{
    protected $table = 'tiket_dapur';

    protected $guarded = [];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function status_tiket_dapur()
    {
        return $this->belongsTo(StatusTiketDapur::class, 'status_tiket_dapur_id');
    }

    public function detail_tiket_dapur()
    {
        return $this->hasMany(DetailTiketDapur::class, 'tiket_dapur_id');
    }
}
