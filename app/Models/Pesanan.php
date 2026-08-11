<?php

namespace App\Models;

use App\Models\PaymentSession;

class Pesanan extends BaseModel
{
    protected $table = 'pesanan';

    protected $guarded = [];

    public function jenis_pesanan()
    {
        return $this->belongsTo(JenisPesanan::class, 'jenis_pesanan_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    public function pelayan()
    {
        return $this->belongsTo(Pengguna::class, 'pelayan_id');
    }

    public function kasir()
    {
        return $this->belongsTo(Pengguna::class, 'kasir_id');
    }

    public function status_pesanan()
    {
        return $this->belongsTo(StatusPesanan::class, 'status_pesanan_id');
    }

    public function detail_pesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'pesanan_id');
    }

    public function jadwal_pesanan()
    {
        return $this->hasOne(JadwalPesanan::class, 'pesanan_id');
    }

    public function tiket_dapur()
    {
        return $this->hasMany(TiketDapur::class, 'pesanan_id');
    }

    public function pengantaran()
    {
        return $this->hasOne(Pengantaran::class, 'pesanan_id');
    }

    public function pengadaan_bahan()
    {
        return $this->hasMany(PengadaanBahan::class, 'pesanan_id');
    }

    public function stok_catering()
    {
        return $this->hasMany(StokCatering::class, 'pesanan_id');
    }

    public function payment_sessions()
    {
        return $this->hasMany(PaymentSession::class, 'pesanan_id');
    }

    /**
     * Persentase uang muka (DP) berdasarkan jenis pesanan:
     * Dine In = bayar penuh (100%), Catering = 50%, Nasi Box = 25%.
     */
    public function persentaseDP(): int
    {
        return match ((int) $this->jenis_pesanan_id) {
            1 => 100, // Dine In / Takeaway
            3 => 25,  // Nasi Box
            default => 50, // Catering
        };
    }

    /**
     * Besaran uang muka (DP) dalam rupiah
     */
    public function nominalDP(): float
    {
        return (float) $this->total_tagihan * ($this->persentaseDP() / 100);
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id_pesanan)) {
                $latest = static::orderBy('id', 'desc')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $prefix = 'XX';
                if ($model->jenis_pesanan_id == 1) $prefix = 'DI';
                elseif ($model->jenis_pesanan_id == 2) $prefix = 'CT';
                elseif ($model->jenis_pesanan_id == 3) $prefix = 'NB';
                
                $model->id_pesanan = $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}

