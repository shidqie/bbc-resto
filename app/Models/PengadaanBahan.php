<?php

namespace App\Models;

class PengadaanBahan extends BaseModel
{
    protected $table = 'pengadaan_bahan';

    protected $guarded = [];

    public function pemasok()
    {
        return $this->belongsTo(Pemasok::class, 'pemasok_id');
    }

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function diajukan_oleh_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'diajukan_oleh');
    }

    public function disetujui_oleh_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'disetujui_oleh');
    }

    public function status_pengadaan()
    {
        return $this->belongsTo(StatusPengadaan::class, 'status_pengadaan_id');
    }

    public function detail_pengadaan_bahan()
    {
        return $this->hasMany(DetailPengadaanBahan::class, 'pengadaan_bahan_id');
    }

    public function penerimaan_bahan()
    {
        return $this->hasMany(PenerimaanBahan::class, 'pengadaan_bahan_id');
    }
}
