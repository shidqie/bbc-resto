<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengaturanTransaksi extends Model
{
    use HasFactory;

    protected $table = 'pengaturan_transaksi';

    protected $fillable = [
        'pajak_aktif',
        'persentase_pajak',
        'layanan_aktif',
        'persentase_layanan',
        'nominal_layanan',
        'diperbarui_oleh',
    ];

    protected $casts = [
        'pajak_aktif' => 'boolean',
        'layanan_aktif' => 'boolean',
        'persentase_pajak' => 'decimal:2',
        'persentase_layanan' => 'decimal:2',
        'nominal_layanan' => 'decimal:2',
    ];

    public function diperbaruiOleh()
    {
        return $this->belongsTo(Pengguna::class, 'diperbarui_oleh');
    }
}
