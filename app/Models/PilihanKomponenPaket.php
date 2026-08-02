<?php

namespace App\Models;

class PilihanKomponenPaket extends BaseModel
{
    protected $table = 'pilihan_komponen_paket';

    protected $guarded = [];

    public function komponen_paket()
    {
        return $this->belongsTo(KomponenPaket::class, 'komponen_paket_id');
    }
}
