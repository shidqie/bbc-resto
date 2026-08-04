<?php

namespace App\Models;

class ItemPaket extends BaseModel
{
    protected $table = 'item_paket';

    protected $guarded = [];

    protected $appends = ['nama_komponen', 'tipe_komponen'];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function menu_terkait()
    {
        return $this->belongsTo(Menu::class, 'menu_id_terkait');
    }

    public function opsi()
    {
        return $this->hasMany(PilihanItemPaket::class, 'item_paket_id')->orderBy('urutan');
    }

    // Alias for backward compatibility
    public function pilihan()
    {
        return $this->opsi();
    }

    public function getNamaKomponenAttribute()
    {
        return $this->attributes['nama_item'] ?? null;
    }

    public function getTipeKomponenAttribute()
    {
        return $this->attributes['tipe_item'] ?? null;
    }
}
