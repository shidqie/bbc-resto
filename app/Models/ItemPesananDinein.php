<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPesananDinein extends Model
{
    protected $fillable = [
        'pesanan_dinein_id',
        'menu_id',
        'qty',
        'status_sajian',
        'catatan',
        'diinput_oleh',
        'diinput_pada',
    ];

    protected $casts = [
        'diinput_pada' => 'datetime',
    ];

    public function pesanan()
    {
        return $this->belongsTo(PesananDinein::class, 'pesanan_dinein_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
