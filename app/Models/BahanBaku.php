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

    public function stok_catering()
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

    public function getStokHarianValAttribute(): float
    {
        $relation = $this->getRelationValue('stok_harian');
        return (float) ($relation?->jumlah_stok ?? 0);
    }

    public function getStokCateringValAttribute(): float
    {
        $relation = $this->getRelationValue('stok_catering_balance');
        return (float) ($relation?->jumlah_stok ?? 0);
    }

    /**
     * Saldo pada jenis persediaan tertentu.
     */
    public function jumlahStokPada(string $jenisPersediaan): float
    {
        return (float) ($this->stok_bahans->firstWhere('jenis_persediaan', $jenisPersediaan)?->jumlah_stok ?? 0);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id_bahan_baku)) {
                $latest = static::orderBy('id', 'desc')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $model->id_bahan_baku = \App\Helpers\IdCodeGenerator::generateBahanBakuId($nextId);
            }
        });

        static::created(function ($model) {
            $min = (float) ($model->stok_minimal ?? 0);
            $initialStok = $min > 0 ? ($min * 5) : 0;

            StokBahan::firstOrCreate(
                ['bahan_baku_id' => $model->id, 'jenis_persediaan' => StokBahan::JENIS_HARIAN],
                ['jumlah_stok' => $initialStok, 'stok_minimal' => $min]
            );

            StokBahan::firstOrCreate(
                ['bahan_baku_id' => $model->id, 'jenis_persediaan' => StokBahan::JENIS_CATERING],
                ['jumlah_stok' => $initialStok, 'stok_minimal' => $min]
            );
        });
    }
}

