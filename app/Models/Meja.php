<?php

namespace App\Models;

use Illuminate\Support\Str;

class Meja extends BaseModel
{
    protected $table = 'meja';

    protected $guarded = [];

    public function status_meja()
    {
        return $this->belongsTo(StatusMeja::class, 'status_meja_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meja) {
            if (empty($meja->qr_token)) {
                $meja->qr_token = Str::random(32);
            }
            if (empty($meja->kode_meja)) {
                $meja->kode_meja = \App\Helpers\IdCodeGenerator::generateMejaId($meja->nomor_meja ?? $meja->id);
            }
        });
    }
}
