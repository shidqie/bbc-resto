<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranCatering extends Model
{
    protected $fillable = [
        'pesanan_catering_id',
        'jenis_pembayaran',
        'jumlah_bayar',
        'metode',
        'bukti_bayar',
        'status',
        'verified_by',
        'verified_at',
        'catatan',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function pesananCatering()
    {
        return $this->belongsTo(PesananCatering::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
