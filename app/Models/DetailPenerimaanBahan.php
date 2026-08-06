<?php

namespace App\Models;

class DetailPenerimaanBahan extends BaseModel
{
    protected $table = 'detail_penerimaan_bahan';

    protected $guarded = [];

    public $timestamps = false;

    public function penerimaan_bahan()
    {
        return $this->belongsTo(PenerimaanBahan::class, 'penerimaan_bahan_id');
    }

    public function detail_pengadaan_bahan()
    {
        return $this->belongsTo(DetailPengadaanBahan::class, 'detail_pengadaan_bahan_id');
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
