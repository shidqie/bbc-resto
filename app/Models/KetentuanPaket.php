<?php

namespace App\Models;

class KetentuanPaket extends BaseModel
{
    protected $table = 'ketentuan_paket';

    protected $guarded = [];

    protected $primaryKey = 'menu_id';

    public $incrementing = false;
}
