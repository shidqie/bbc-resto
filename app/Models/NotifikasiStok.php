<?php

namespace App\Models;

class NotifikasiStok extends BaseModel
{
    protected $table = 'notifikasi_stoks';

    protected $guarded = [];

    protected $casts = [
        'stok_saat_ini' => 'decimal:3',
        'stok_minimal' => 'decimal:3',
        'dibaca' => 'boolean',
        'dibaca_pada' => 'datetime',
    ];

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class);
    }

    public function dibacaOleh()
    {
        return $this->belongsTo(Pengguna::class, 'dibaca_oleh');
    }

    public function scopeUnread($query)
    {
        return $query->where('dibaca', false);
    }

    public function scopeByType($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }
}