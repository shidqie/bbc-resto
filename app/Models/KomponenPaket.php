<?php

namespace App\Models;

class KomponenPaket extends BaseModel
{
    protected $table = 'komponen_paket';
    protected $guarded = [];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function opsi()
    {
        return $this->hasMany(PilihanKomponenPaket::class, 'komponen_paket_id')->orderBy('urutan');
    }
}
