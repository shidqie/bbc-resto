<?php

namespace App\Models;

class Pengantaran extends BaseModel
{
    protected $table = 'pengantaran';

    protected $guarded = [];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function status_pengantaran()
    {
        return $this->belongsTo(StatusPengantaran::class, 'status_pengantaran_id');
    }

    public function ditugaskan_kepada_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'ditugaskan_kepada');
    }
}
