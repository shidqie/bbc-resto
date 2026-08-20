<?php

namespace App\Models;

class Pemasok extends BaseModel
{
    protected $table = 'pemasok';

    protected $guarded = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->kode_pemasok)) {
                $latest = static::orderBy('id', 'desc')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $model->kode_pemasok = \App\Helpers\IdCodeGenerator::generateSupplierId($nextId);
            }
        });
    }

    public function getKodeSupplierAttribute(): string
    {
        return $this->kode_pemasok ?: \App\Helpers\IdCodeGenerator::generateSupplierId($this->id);
    }
}
