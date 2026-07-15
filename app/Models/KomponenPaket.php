<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KomponenPaket extends Model
{
    protected $fillable = ['paket_catering_id', 'nama_komponen', 'tipe', 'urutan'];

    public function paketCatering()
    {
        return $this->belongsTo(PaketCatering::class);
    }

    public function opsi()
    {
        return $this->hasMany(OpsiKomponen::class);
    }
}
