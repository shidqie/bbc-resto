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

    public function diverifikasi_oleh_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'diverifikasi_oleh');
    }

    public function detail_penerimaan_bahan()
    {
        return $this->hasMany(DetailPenerimaanBahan::class, 'penerimaan_bahan_id');
    }

    public function getStatusNamaAttribute(): string
    {
        return match ($this->status) {
            'menunggu_penerimaan' => 'Menunggu Penerimaan',
            'sedang_diperiksa' => 'Sedang Diperiksa',
            'diterima_sebagian' => 'Diterima Sebagian',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            default => ucwords(str_replace('_', ' ', (string) $this->status)),
        };
    }
}
