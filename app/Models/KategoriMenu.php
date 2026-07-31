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
}
