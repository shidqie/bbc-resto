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

    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class, 'pesanan_id');
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
                $model->id_pesanan = \App\Helpers\IdCodeGenerator::generatePesananId($model->tanggal_pesanan ?? $model->dibuat_pada ?? now());
            }
        });
    }
}

