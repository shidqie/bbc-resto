<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResepMenu extends Model
{
    protected $table = 'resep_menus';
    
    protected $fillable = [
        'menu_id',
        'bahan_baku_id',
        'jumlah_kebutuhan',
        'satuan',
        'keterangan'
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function bahanBaku()
    {
        return $this->belongsTo(BahanBaku::class);
    }
}
