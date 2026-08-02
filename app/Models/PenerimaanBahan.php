<?php

namespace App\Models;

class PenerimaanBahan extends BaseModel
{
    protected $table = 'penerimaan_bahan';

    protected $guarded = [];

    public $timestamps = false;

    public function pengadaan_bahan()
    {
        return $this->belongsTo(PengadaanBahan::class, 'pengadaan_bahan_id');
    }

    public function diterima_oleh_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'diterima_oleh');
    }

    public function detail_penerimaan_bahan()
    {
        return $this->hasMany(DetailPenerimaanBahan::class, 'penerimaan_bahan_id');
    }
}
