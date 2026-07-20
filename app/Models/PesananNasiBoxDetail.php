<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananNasiBoxDetail extends Model
{
    protected $fillable = [
        'pesanan_nasi_box_id',
        'komponen_paket_id',
        'menu_id'
    ];

    public function pesananNasiBox()
    {
        return $this->belongsTo(PesananNasiBox::class);
    }

    public function komponenPaket()
    {
        return $this->belongsTo(KomponenPaket::class, 'komponen_paket_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
