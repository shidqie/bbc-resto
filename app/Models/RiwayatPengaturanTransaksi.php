<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiwayatPengaturanTransaksi extends Model
{
    use HasFactory;

    protected $table = 'riwayat_pengaturan_transaksi';

    public $timestamps = false; // We use dibuat_pada only

    protected $fillable = [
        'nilai_lama',
        'nilai_baru',
        'diubah_oleh',
        'dibuat_pada',
    ];

    protected $casts = [
        'nilai_lama' => 'array',
        'nilai_baru' => 'array',
        'dibuat_pada' => 'datetime',
    ];

    public function diubahOleh()
    {
        return $this->belongsTo(Pengguna::class, 'diubah_oleh');
    }
}
