<?php

namespace App\Models;

class Pembayaran extends BaseModel
{
    protected $table = 'pembayaran';
    protected $guarded = [];

    public function pesanan() { return $this->belongsTo(Pesanan::class, 'pesanan_id'); }

    public function metode_pembayaran() { return $this->belongsTo(MetodePembayaran::class, 'metode_pembayaran_id'); }

    public function status_pembayaran() { return $this->belongsTo(StatusPembayaran::class, 'status_pembayaran_id'); }

    public function jenis_pembayaran() { return $this->belongsTo(JenisPembayaran::class, 'jenis_pembayaran_id'); }

    public function diproses_oleh_pengguna() { return $this->belongsTo(Pengguna::class, 'diproses_oleh'); }
}
