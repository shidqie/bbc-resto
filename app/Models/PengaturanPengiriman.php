<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanPengiriman extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_pengiriman';

    protected $fillable = [
        'tarif_dasar',
        'tarif_per_km',
        'status_aktif',
        'diperbarui_oleh',
    ];

    protected $casts = [
        'tarif_per_km' => 'decimal:2',
        'status_aktif' => 'boolean',
    ];

    public function diperbaruiOleh()
    {
        return $this->belongsTo(Pengguna::class, 'diperbarui_oleh');
    }
}
