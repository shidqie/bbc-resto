<?php

namespace App\Models;

class KategoriMenu extends BaseModel
{
    protected $table = 'kategori_menu';

    protected $guarded = [];

    public function menu()
    {
        return $this->hasMany(Menu::class, 'kategori_menu_id', 'id');
    }

    public function getKodeKategoriAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generateKategoriMenuId($this->id);
    }

    public function getIdKategoriMenuAttribute(): string
    {
        return $this->kode_kategori;
    }
}
