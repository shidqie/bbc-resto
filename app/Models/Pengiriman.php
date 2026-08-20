<?php

namespace App\Models;

class Pengiriman extends BaseModel
{
    protected $table = 'pengiriman';

    protected $guarded = [];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    public function status_pengiriman()
    {
        return $this->belongsTo(StatusPengiriman::class, 'status_pengiriman_id');
    }

    public function ditugaskan_kepada_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'ditugaskan_kepada');
    }

    public static function hitungOngkir($jarakKm, $pax)
    {
        // Hitung jarak yang perlu dibayar berdasarkan tier gratis ongkir
        // >= 200 pax: 20 km pertama gratis, bayar kelebihan dari 20 km
        // 100–199 pax: 10 km pertama gratis, bayar kelebihan dari 10 km
        // < 100 pax: tidak ada gratis, bayar dari km pertama
        
        if ($pax >= 200) {
            $jarakHitung = max(0, $jarakKm - 20);
        } elseif ($pax >= 100) {
            $jarakHitung = max(0, $jarakKm - 10);
        } else {
            $jarakHitung = $jarakKm;
        }

        if ($jarakHitung <= 0) {
            return 0;
        }

        return 10000 + ($jarakHitung * 3000);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->nomor_pengiriman)) {
                $model->nomor_pengiriman = \App\Helpers\IdCodeGenerator::generatePengirimanId($model->jadwal_pengiriman ?? $model->dibuat_pada ?? now());
            }
        });
    }
}
