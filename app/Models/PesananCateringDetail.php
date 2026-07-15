<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananCateringDetail extends Model
{
    protected $fillable = [
        'pesanan_id',
        'komponen_id',
        'menu_id_terpilih'
    ];

    public function pesanan()
    {
        return $this->belongsTo(PesananCatering::class, 'pesanan_id');
    }

    public function komponen()
    {
        return $this->belongsTo(KomponenPaket::class, 'komponen_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id_terpilih');
    }
}
