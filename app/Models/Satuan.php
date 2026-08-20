<?php

namespace App\Models;

class Satuan extends BaseModel
{
    protected $table = 'satuan';

    protected $guarded = [];

    public $timestamps = false;

    public function bahan_bakus()
    {
        return $this->hasMany(BahanBaku::class, 'satuan_id');
    }

    public function getKodeSatuanAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generateSatuanId($this->id);
    }
}
