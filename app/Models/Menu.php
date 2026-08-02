<?php

namespace App\Models;

class Menu extends BaseModel
{
    protected $table = 'menu';

    protected $guarded = [];

    public function jenis_menu()
    {
        return $this->belongsTo(JenisMenu::class, 'jenis_menu_id');
    }

    public function kategori_menu()
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_menu_id');
    }

    public function resep_menu()
    {
        return $this->hasMany(ResepMenu::class, 'menu_id');
    }

    public function resep()
    {
        return $this->hasMany(ResepMenu::class, 'menu_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_menu_id');
    }

    public function komponen_paket()
    {
        return $this->hasMany(KomponenPaket::class, 'menu_id')->orderBy('urutan');
    }

    public function ketentuan_paket()
    {
        return $this->hasOne(KetentuanPaket::class, 'menu_id');
    }
}
