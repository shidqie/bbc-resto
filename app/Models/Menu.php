<?php

namespace App\Models;

class Menu extends BaseModel
{
    protected $table = 'menu';

    protected $guarded = [];

    public function jenis_menu()
    {
        return $this->belongsTo(JenisMenu::class, 'jenis_menu_id');
    }

    public function kategori_menu()
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_menu_id');
    }

    public function resep_menu()
    {
        return $this->hasMany(ResepMenu::class, 'menu_id');
    }

    public function resep()
    {
        return $this->hasMany(ResepMenu::class, 'menu_id');
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_menu_id');
    }

    public function item_paket()
    {
        return $this->hasMany(ItemPaket::class, 'menu_id')->orderBy('urutan');
    }

    // Alias for backward compatibility
    public function komponen_paket()
    {
        return $this->hasMany(ItemPaket::class, 'menu_id')->orderBy('urutan');
    }

    public function ketentuan_paket()
    {
        return $this->hasOne(KetentuanPaket::class, 'menu_id');
    }

    public function getFotoUrlAttribute()
    {
        if (!$this->foto) {
            return null;
        }
        if (str_starts_with($this->foto, 'http://') || str_starts_with($this->foto, 'https://') || str_starts_with($this->foto, '/')) {
            return $this->foto;
        }
        if (str_starts_with($this->foto, 'images/')) {
            return asset($this->foto);
        }
        return \Illuminate\Support\Facades\Storage::url($this->foto);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id_menu)) {
                $latest = static::orderBy('id', 'desc')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                if (in_array((int)$model->jenis_menu_id, [2, 3])) {
                    $model->id_menu = \App\Helpers\IdCodeGenerator::generatePaketId($model->jenis_menu_id, $nextId);
                } else {
                    $model->id_menu = \App\Helpers\IdCodeGenerator::generateMenuId($nextId);
                }
            }
        });
    }
}
