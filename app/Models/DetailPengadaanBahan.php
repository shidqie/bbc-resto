<?php

namespace App\Models;

class DetailPengadaanBahan extends BaseModel
{
    protected $table = 'detail_pengadaan_bahan';
    protected $guarded = [];
    public $timestamps = false;

    public function pengadaan_bahan() { return $this->belongsTo(PengadaanBahan::class, 'pengadaan_bahan_id'); }
    public function bahan_baku() { return $this->belongsTo(BahanBaku::class, 'bahan_baku_id'); }
    public function satuan() { return $this->belongsTo(Satuan::class, 'satuan_id'); }
    public function detail_purchase_order() { return $this->hasMany(DetailPurchaseOrder::class, 'detail_pengadaan_bahan_id'); }
    public function detail_penerimaan_bahan() { return $this->hasMany(DetailPenerimaanBahan::class, 'detail_pengadaan_bahan_id'); }
}
