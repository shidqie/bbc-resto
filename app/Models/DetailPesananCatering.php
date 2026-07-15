<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPesananCatering extends Model
{
    protected $fillable = [
        'pesanan_catering_id',
        'komponen_paket_id',
        'menu_id',
    ];

    public function pesananCatering()
    {
        return $this->belongsTo(PesananCatering::class);
    }

    public function komponenPaket()
    {
        return $this->belongsTo(KomponenPaket::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
