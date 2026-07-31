<?php

namespace App\Models;

class PembayaranDinein extends BaseModel
{
    protected $table = 'pembayaran';
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->metode_pembayaran_id)) {
                $model->metode_pembayaran_id = 1; // 1 = Tunai
            }
            if (empty($model->status_pembayaran_id)) {
                $model->status_pembayaran_id = 3; // 3 = Lunas
            }
            if (empty($model->waktu_pembayaran)) {
                $model->waktu_pembayaran = now();
            }
        });
    }

    public function setPesananDineinIdAttribute($value)
    {
        $this->attributes['pesanan_id'] = $value;
    }

    public function getPesananDineinIdAttribute()
    {
        return $this->attributes['pesanan_id'] ?? null;
    }

    public function setMetodeBayarAttribute($value)
    {
        $this->attributes['metode_pembayaran_id'] = ($value === 'qris' || $value == 2) ? 2 : 1;
    }

    public function setTotalAttribute($value)
    {
        $this->attributes['jumlah_bayar'] = $value;
    }

    public function setDiprosesOlehAttribute($value)
    {
        $this->attributes['diinput_oleh_pengguna_id'] = $value;
    }

    public function setDiprosesPadaAttribute($value)
    {
        $this->attributes['waktu_pembayaran'] = $value;
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status_pembayaran_id'] = ($value === 'void' || $value == 4) ? 4 : 3;
    }

    public function pesanan()
    {
        return $this->belongsTo(PesananDinein::class, 'pesanan_id');
    }
}
