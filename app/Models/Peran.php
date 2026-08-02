<?php

namespace App\Models;

class Peran extends BaseModel
{
    protected $table = 'peran';

    protected $guarded = [];

    public function pengguna()
    {
        return $this->hasMany(Pengguna::class, 'peran_id');
    }
}
