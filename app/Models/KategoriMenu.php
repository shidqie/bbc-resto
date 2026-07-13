<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriMenu extends Model
{
    protected $fillable = ['nama'];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }
}
