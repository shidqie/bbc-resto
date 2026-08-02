<?php

namespace App\Models;

class ItemPesananDinein extends BaseModel
{
    protected $table = 'detail_pesanan';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->harga_satuan) && ! empty($model->menu_id)) {
                $menu = Menu::find($model->menu_id);
                if ($menu) {
                    $model->harga_satuan = $menu->harga_jual;
                    $qty = $model->jumlah ?? $model->qty ?? 1;
                    $model->subtotal = $model->harga_satuan * $qty;
                }
            }
        });
    }

    public function setPesananDineinIdAttribute($value)
    {
        $this->attributes['pesanan_id'] = $value;
    }

    public function getPesananDineinIdAttribute()
    {
        return $this->attributes['pesanan_id'] ?? null;
    }

    public function setMenuIdAttribute($value)
    {
        $this->attributes['menu_id'] = $value;
    }

    public function getMenuIdAttribute()
    {
        return $this->attributes['menu_id'] ?? null;
    }

    public function setQtyAttribute($value)
    {
        $this->attributes['jumlah'] = $value;
        if (! empty($this->attributes['harga_satuan'])) {
            $this->attributes['subtotal'] = $this->attributes['harga_satuan'] * $value;
        }
    }

    public function getQtyAttribute()
    {
        return $this->attributes['jumlah'] ?? 1;
    }

    public function setDiinputOlehAttribute($value)
    {
        // Virtual attribute ignored for SQL columns
    }

    public function setDiinputPadaAttribute($value)
    {
        // Virtual attribute ignored for SQL columns
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function pesanan()
    {
        return $this->belongsTo(PesananDinein::class, 'pesanan_id');
    }
}
