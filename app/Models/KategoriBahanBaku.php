<?php

namespace App\Models;

class KategoriBahanBaku extends BaseModel
{
    protected $table = 'kategori_bahan_baku';

    protected $guarded = [];

    public $timestamps = false;

    public function bahan_bakus()
    {
        return $this->hasMany(BahanBaku::class, 'kategori_bahan_baku_id');
    }
}
