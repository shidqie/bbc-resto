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

    /**
     * Saldo per jenis persediaan (tabel stok_bahan).
     */
    public function stok_harian()
    {
        return $this->hasOne(StokBahan::class, 'bahan_baku_id')->where('jenis_persediaan', StokBahan::JENIS_HARIAN);
    }

    public function stok_catering_balance()
    {
        return $this->hasOne(StokBahan::class, 'bahan_baku_id')->where('jenis_persediaan', StokBahan::JENIS_CATERING);
    }

    public function getTotalStokAttribute()
    {
        $harian = $this->stok_harian ? (float) $this->stok_harian->jumlah_stok : 0;
        $catering = $this->stok_catering_balance ? (float) $this->stok_catering_balance->jumlah_stok : 0;
        return $harian + $catering;
    }

    public function stok_bahans()
    {
        return $this->hasMany(StokBahan::class, 'bahan_baku_id');
    }

    public function getStokHarianAttribute(): float
    {
        return (float) ($this->stok_harian?->jumlah_stok ?? 0);
    }

    public function getStokCateringAttribute(): float
    {
        return (float) ($this->stok_catering_balance?->jumlah_stok ?? 0);
    }

    /**
     * Saldo pada jenis persediaan tertentu.
     */
    public function jumlahStokPada(string $jenisPersediaan): float
    {
        return (float) ($this->stok_bahans->firstWhere('jenis_persediaan', $jenisPersediaan)?->jumlah_stok ?? 0);
    }
}
