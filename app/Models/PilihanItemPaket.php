<?php

namespace App\Models;

class PilihanItemPaket extends BaseModel
{
    protected $table = 'pilihan_item_paket';

    protected $guarded = [];

    public function item_paket()
    {
        return $this->belongsTo(ItemPaket::class, 'item_paket_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    // Alias for backward compatibility
    public function komponen_paket()
    {
        return $this->belongsTo(ItemPaket::class, 'item_paket_id');
    }
}
