<?php

namespace App\Models;

class JadwalPesanan extends BaseModel
{
    protected $table = 'jadwal_pesanan';

    protected $guarded = [];

    protected $primaryKey = 'pesanan_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $casts = ['tanggal_acara' => 'datetime'];
}
