<?php

namespace App\Models;

class BahanBaku extends BaseModel
{
    protected $table = 'bahan_baku';

    protected $guarded = [];

    public function kategori_bahan_baku()
    {
        return $this->belongsTo(KategoriBahanBaku::class, 'kategori_bahan_baku_id');
    }

    public function kategoriBahan()
    {
        return $this->belongsTo(KategoriBahanBaku::class, 'kategori_bahan_baku_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function stok_relasi()
    {
        return $this->hasOne(StokBahanBaku::class, 'bahan_baku_id');
    }

    public function stok()
    {
        return $this->hasOne(StokBahanBaku::class, 'bahan_baku_id');
    }

    public function stok_bahan_baku()
    {
        return $this->hasOne(StokBahanBaku::class, 'bahan_baku_id');
    }

    public function getJumlahStokAttribute()
    {
        return (float) ($this->stok_relasi->jumlah_stok ?? 0);
    }

    public function stok_caterings()
    {
        return $this->hasMany(StokCatering::class, 'bahan_baku_id');
    }
}
