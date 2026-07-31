<?php

namespace App\Models;

class DetailPesanan extends BaseModel
{
    protected $table = 'detail_pesanan';
    protected $guarded = [];

    public function pesanan() { return $this->belongsTo(Pesanan::class, 'pesanan_id'); }

    public function menu() { return $this->belongsTo(Menu::class, 'menu_id'); }
}
