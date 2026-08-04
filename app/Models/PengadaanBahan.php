<?php

namespace App\Models;

class PengadaanBahan extends BaseModel
{
    protected $table = 'pengadaan_bahan';

    protected $guarded = [];

    protected $casts = [
        'tanggal_pengadaan' => 'date:Y-m-d',
        'perkiraan_tanggal_datang' => 'date:Y-m-d',
    ];

    public const JENIS_HARIAN = 'harian';

    public const JENIS_CATERING = 'catering';

    public function scopeHarian($query)
    {
        return $query->where('jenis_pengadaan', self::JENIS_HARIAN);
    }

    public function scopeCatering($query)
    {
        return $query->where('jenis_pengadaan', self::JENIS_CATERING);
    }

    public function getJenisPengadaanNamaAttribute(): string
    {
        return $this->jenis_pengadaan === self::JENIS_CATERING ? 'Catering' : 'Harian';
    }

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
