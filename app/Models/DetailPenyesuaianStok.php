<?php

namespace App\Models;

class DetailPenyesuaianStok extends BaseModel
{
    protected $table = 'detail_penyesuaian_stok';

    protected $guarded = [];

    public $timestamps = false;

    public function penyesuaian_stok()
    {
        return $this->belongsTo(PenyesuaianStok::class, 'penyesuaian_stok_id');
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
}
