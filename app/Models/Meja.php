<?php

namespace App\Models;

class Meja extends BaseModel
{
    protected $table = 'meja';
    protected $guarded = [];

    public function status_meja() { return $this->belongsTo(StatusMeja::class, 'status_meja_id'); }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meja) {
            if (empty($meja->qr_token)) {
                $meja->qr_token = \Illuminate\Support\Str::random(32);
            }
            if (empty($meja->kode_meja)) {
                // Generate a simple code like MJ-001 based on nomor_meja or random if not available
                $number = preg_replace('/[^0-9]/', '', $meja->nomor_meja);
                if ($number) {
                    $meja->kode_meja = 'MJ-' . str_pad($number, 3, '0', STR_PAD_LEFT);
                } else {
                    $meja->kode_meja = 'MJ-' . strtoupper(\Illuminate\Support\Str::random(4));
                }
            }
        });
    }
}
