<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPaketCatering extends Model
{
    protected $fillable = [
        'paket_catering_id',
        'bahan_baku_id',
        'jumlah_kebutuhan',
        'keterangan',
    ];

    public function paketCatering()
    {
        return $this->belongsTo(PaketCatering::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
