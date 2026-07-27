<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaketCatering extends Model
{
    protected $fillable = [
        'nama_paket',
        'jenis_paket',
        'harga',
        'deskripsi',
        'foto',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function komponens()
    {
        return $this->hasMany(KomponenPaket::class)->orderBy('urutan');
    }

    public function pesanans()
    {
        return $this->hasMany(PesananCatering::class);
    }
}
