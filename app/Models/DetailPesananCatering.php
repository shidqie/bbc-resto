<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPesananCatering extends Model
{
    protected $fillable = [
        'pesanan_catering_id',
        'bahan_baku_id',
        'jumlah_digunakan',
    ];

    public function pesananCatering()
    {
        return $this->belongsTo(PesananCatering::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
