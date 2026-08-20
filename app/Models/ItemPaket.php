<?php

namespace App\Models;

class ItemPaket extends BaseModel
{
    protected $table = 'item_paket';

    protected $guarded = [];

    protected $appends = ['nama_komponen', 'tipe_komponen'];

    const TIPE_WAJIB = 'wajib';
    const TIPE_PILIHAN = 'pilihan';
    const TIPE_SEMUA_DIDAPAT = 'semua_didapat';

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    public function menu_terkait()
    {
        return $this->belongsTo(Menu::class, 'menu_id_terkait');
    }

    public function opsi()
    {
        return $this->hasMany(PilihanItemPaket::class, 'item_paket_id')->orderBy('urutan');
    }

    // Alias for backward compatibility
    public function pilihan()
    {
        return $this->opsi();
    }

    public function getNamaKomponenAttribute()
    {
        return $this->menu_terkait ? $this->menu_terkait->nama_menu : ($this->attributes['nama_item'] ?? null);
    }

    public function getNamaItemAttribute($value)
    {
        return $this->menu_terkait ? $this->menu_terkait->nama_menu : $value;
    }

    public function getTipeKomponenAttribute()
    {
        return $this->attributes['tipe_item'] ?? null;
    }

    public function getIdDetailPaketAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generateDetailPaketId($this->id);
    }

    public function getKodeDetailPaketAttribute(): string
    {
        return $this->id_detail_paket;
    }
}
