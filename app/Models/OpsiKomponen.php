<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpsiKomponen extends Model
{
    protected $fillable = ['komponen_paket_id', 'menu_id'];

    public function komponenPaket()
    {
        return $this->belongsTo(KomponenPaket::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
