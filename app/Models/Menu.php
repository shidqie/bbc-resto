<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['kategori_menu_id', 'nama', 'jenis_menu', 'deskripsi', 'harga', 'foto', 'status'];

    public function kategori()
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_menu_id');
    }

    public function resep()
    {
        return $this->hasMany(ResepMenu::class);
    }
}
