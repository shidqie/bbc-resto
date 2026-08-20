<?php

namespace App\Models;

/**
 * Saldo bahan baku yang dipisah per jenis persediaan.
 *
 * Jenis persediaan:
 *  - harian  : Dine-In dan Nasi Box
 *  - catering: Catering
 *
 * Master bahan (bahan_baku) tetap satu. Unique constraint gabungan
 * (bahan_baku_id, jenis_persediaan) menjamin tidak ada duplikasi saldo.
 */
class StokBahan extends BaseModel
{
    protected $table = 'stok_bahan';

    protected $guarded = [];

    public $timestamps = false;

    public const JENIS_HARIAN = 'harian';

    public const JENIS_CATERING = 'catering';

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function scopeHarian($query)
    {
        return $query->where('jenis_persediaan', self::JENIS_HARIAN);
    }

    public function scopeCatering($query)
    {
        return $query->where('jenis_persediaan', self::JENIS_CATERING);
    }

    public function getJenisPersediaanNamaAttribute(): string
    {
        return $this->jenis_persediaan === self::JENIS_CATERING ? 'Catering' : 'Harian';
    }

    public function getIdStokAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generateStokId($this->id);
    }

    public function getKodeStokAttribute(): string
    {
        return $this->id_stok;
    }
}
