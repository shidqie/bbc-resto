<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananCateringAddon extends Model
{
    protected $fillable = [
        'pesanan_id',
        'layanan_tambahan_id',
        'catatan'
    ];

    public function pesanan()
    {
        return $this->belongsTo(PesananCatering::class, 'pesanan_id');
    }

    public function layananTambahan()
    {
        return $this->belongsTo(LayananTambahan::class, 'layanan_tambahan_id');
    }
}
