<?php

namespace App\Models;

class PenyesuaianStok extends BaseModel
{
    protected $table = 'penyesuaian_stok';
    protected $guarded = [];

    public function dibuat_oleh_pengguna() { return $this->belongsTo(Pengguna::class, 'dibuat_oleh'); }

    public function disetujui_oleh_pengguna() { return $this->belongsTo(Pengguna::class, 'disetujui_oleh'); }

    public function detail_penyesuaian_stok() { return $this->hasMany(DetailPenyesuaianStok::class, 'penyesuaian_stok_id'); }
}
