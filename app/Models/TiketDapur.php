<?php

namespace App\Models;

class TiketDapur extends BaseModel
{
    protected $table = 'tiket_dapur';

    protected $guarded = [];

    protected $casts = [
        'dicetak_pada' => 'datetime',
        'diproses_pada' => 'datetime',
        'siap_pada' => 'datetime',
        'selesai_pada' => 'datetime',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    public function status_tiket_dapur()
    {
        return $this->belongsTo(StatusTiketDapur::class, 'status_tiket_dapur_id');
    }

    public function detail_tiket_dapur()
    {
        return $this->hasMany(DetailTiketDapur::class, 'tiket_dapur_id');
    }

    public function diprosesOleh()
    {
        return $this->belongsTo(Pengguna::class, 'diproses_oleh');
    }

    public function diselesaikanOleh()
    {
        return $this->belongsTo(Pengguna::class, 'diselesaikan_oleh');
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($tiket) {
            if (empty($tiket->nomor_tiket)) {
                $lastTiket = self::latest('id')->first();
                $nextNumber = $lastTiket ? $lastTiket->id + 1 : 1;
                $tiket->nomor_tiket = 'KOT-'.date('Ymd').'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            }
            if (empty($tiket->status_tiket_dapur_id)) {
                $tiket->status_tiket_dapur_id = 1; // MENUNGGU
            }
            if (empty($tiket->dicetak_pada)) {
                $tiket->dicetak_pada = now();
            }
        });
    }
}