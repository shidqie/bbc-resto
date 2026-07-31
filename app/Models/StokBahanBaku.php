<?php

namespace App\Models;

class StokBahanBaku extends BaseModel
{
    protected $table = 'stok_bahan_baku';
    protected $guarded = [];
    protected $primaryKey = 'bahan_baku_id';
    public $incrementing = false;
    public $timestamps = false;

}
