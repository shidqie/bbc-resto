<?php

namespace App\Models;

class ResepMenu extends BaseModel
{
    protected $table = 'resep_menu';

    protected $guarded = [];

    // Alias: kolom di DB adalah 'jumlah', tapi kode banyak pakai ->jumlah_kebutuhan
    public function getJumlahKebutuhanAttribute()
    {
        return $this->jumlah;
    }

    public function setJumlahKebutuhanAttribute($value)
    {
        $this->attributes['jumlah'] = $value;
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function getIdResepAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generateResepId($this->id);
    }

    public function getKodeResepAttribute(): string
    {
        return $this->id_resep;
    }
}
