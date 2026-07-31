<?php

namespace App\Models;

class ResepMenu extends BaseModel
{
    protected $table = 'resep_menu';
    protected $guarded = [];

    public function menu() { return $this->belongsTo(Menu::class, 'menu_id'); }

    public function bahan_baku() { return $this->belongsTo(BahanBaku::class, 'bahan_baku_id'); }
    public function bahanBaku() { return $this->belongsTo(BahanBaku::class, 'bahan_baku_id'); }

    public function satuan() { return $this->belongsTo(Satuan::class, 'satuan_id'); }
}
