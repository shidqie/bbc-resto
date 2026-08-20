<?php

namespace App\Models;

class PesananDinein extends BaseModel
{
    protected $table = 'pesanan';

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->jenis_pesanan_id)) {
                $model->jenis_pesanan_id = 1; // 1 = Dine In
            }
            if (empty($model->status_pesanan_id)) {
                $model->status_pesanan_id = 1; // 1 = Menunggu
            }
            if (empty($model->tanggal_pesanan)) {
                $model->tanggal_pesanan = now();
            }
        });
    }

    public function getKodePesananAttribute()
    {
        return $this->attributes['id_pesanan'] ?? ($this->attributes['kode_pesanan'] ?? null);
    }

    public function setKodePesananAttribute($value)
    {
        $this->attributes['id_pesanan'] = $value;
    }

    public function setNamaKonsumenAttribute($value)
    {
        if ($value) {
            $catatan = $this->attributes['catatan'] ?? '';
            $this->attributes['catatan'] = trim("Pemesan: {$value} | {$catatan}", ' |');
        }
    }

    public function getNamaKonsumenAttribute()
    {
        if (preg_match('/Pemesan:\s*([^|]+)/', $this->attributes['catatan'] ?? '', $matches)) {
            return trim($matches[1]);
        }

        return 'Pelanggan';
    }

    public function setJumlahTamuAttribute($value)
    {
        // Virtual attribute ignored for SQL columns
    }

    public function getJumlahTamuAttribute()
    {
        return 1;
    }

    public function setDibukaOlehAttribute($value)
    {
        $this->attributes['pelayan_id'] = $value;
    }

    public function getDibukaOlehAttribute()
    {
        return $this->attributes['pelayan_id'] ?? null;
    }

    public function setDibukaPadaAttribute($value)
    {
        // Handled by tanggal_pesanan
    }

    public function getDibukaPadaAttribute()
    {
        return $this->attributes['tanggal_pesanan'] ?? null;
    }

    public function getStatusAttribute()
    {
        return match ((int) ($this->attributes['status_pesanan_id'] ?? 1)) {
            5 => 'lunas',
            6 => 'batal',
            default => 'menunggu_pembayaran'
        };
    }

    public function setStatusAttribute($value)
    {
        $id = match ($value) {
            'lunas', 'selesai' => 5,
            'batal', 'dibatalkan' => 6,
            default => 1
        };
        $this->attributes['status_pesanan_id'] = $id;
    }

    public function items()
    {
        return $this->hasMany(ItemPesananDinein::class, 'pesanan_id');
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    public function detail_pesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }
}
