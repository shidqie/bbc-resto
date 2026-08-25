<?php

namespace App\Models;

class DetailPesanan extends BaseModel
{
    protected $table = 'detail_pesanan';

    protected $guarded = [];

    protected $casts = [
        'is_tambahan' => 'boolean',
        'batch_pesanan' => 'integer',
        'waktu_dipesan' => 'datetime',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function pilihan_pesanan_catering()
    {
        return $this->hasMany(PilihanPesananCatering::class, 'detail_pesanan_id');
    }

    public function getIdDetailPesananAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generateDetailPesananId($this->id);
    }

    public function getKodeDetailPesananAttribute(): string
    {
        return $this->id_detail_pesanan;
    }
}
