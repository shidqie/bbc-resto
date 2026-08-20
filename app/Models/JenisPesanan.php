<?php

namespace App\Models;

class JenisPesanan extends BaseModel
{
    protected $table = 'jenis_pesanan';

    protected $guarded = [];

    public $timestamps = false;

    public function getKodeJenisAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generateJenisPesananId($this->id);
    }
}
