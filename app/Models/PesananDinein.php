<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananDinein extends Model
{
    protected $fillable = [
        'meja_id',
        'nama_konsumen',
        'jumlah_tamu',
        'status',
        'sub_status',
        'dibuka_oleh',
        'dibuka_pada',
        'dibayar_pada',
    ];

    protected $casts = [
        'dibuka_pada' => 'datetime',
        'dibayar_pada' => 'datetime',
    ];

    public function meja()
    {
        return $this->belongsTo(Meja::class);
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'dibuka_oleh');
    }

    public function items()
    {
        return $this->hasMany(ItemPesananDinein::class, 'pesanan_dinein_id');
    }

    public function pembayaran()
    {
        return $this->hasOne(PembayaranDinein::class, 'pesanan_dinein_id');
    }
}
