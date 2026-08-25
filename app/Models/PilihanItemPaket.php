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
    public function getNamaPilihanAttribute($value)
    {
        return $this->menu ? $this->menu->nama_menu : $value;
    }

    public function getFotoAttribute($value)
    {
        return $value ?: ($this->menu ? $this->menu->foto : null);
    }

    public function getFotoUrlAttribute()
    {
        $foto = $this->foto;
        if (!$foto) {
            return null;
        }
        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://') || str_starts_with($foto, '/')) {
            return $foto;
        }
        if (str_starts_with($foto, 'images/')) {
            return asset($foto);
        }
        return \Illuminate\Support\Facades\Storage::url($foto);
    }
}
