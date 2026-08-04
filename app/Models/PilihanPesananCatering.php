<?php

namespace App\Models;

class PilihanPesananCatering extends BaseModel
{
    protected $table = 'pilihan_pesanan_catering';

    protected $guarded = [];

    public function detail_pesanan()
    {
        return $this->belongsTo(DetailPesanan::class, 'detail_pesanan_id');
    }

    public function komponen_paket()
    {
        return $this->belongsTo(ItemPaket::class, 'item_paket_id');
    }

    public function pilihan_komponen_paket()
    {
        return $this->belongsTo(PilihanItemPaket::class, 'pilihan_item_paket_id');
    }
}
