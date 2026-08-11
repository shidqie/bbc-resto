<?php

namespace App\Models;

class PurchaseOrder extends BaseModel
{
    protected $table = 'purchase_order';

    protected $guarded = [];

    public const MENUNGGU_BARANG = 'menunggu_barang';
    public const DITERIMA_SEBAGIAN = 'diterima_sebagian';
    public const SELESAI = 'selesai';
    public const DIBATALKAN = 'dibatalkan';

    public function pengadaan_bahan()
    {
        return $this->belongsTo(PengadaanBahan::class, 'pengadaan_bahan_id');
    }

    public function dibuat_oleh_pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'dibuat_oleh');
    }

    public function detail_purchase_order()
    {
        return $this->hasMany(DetailPurchaseOrder::class, 'purchase_order_id');
    }

    public function penerimaan_bahan()
    {
        return $this->hasMany(PenerimaanBahan::class, 'purchase_order_id');
    }

    public function getStatusNamaAttribute(): string
    {
        return match ($this->status) {
            self::MENUNGGU_BARANG => 'Menunggu Barang',
            self::DITERIMA_SEBAGIAN => 'Diterima Sebagian',
            self::SELESAI => 'Selesai',
            self::DIBATALKAN => 'Dibatalkan',
            default => ucwords(str_replace('_', ' ', (string) $this->status)),
        };
    }

    public function getStatusWarnaAttribute(): string
    {
        return match ($this->status) {
            self::MENUNGGU_BARANG => 'warning',
            self::DITERIMA_SEBAGIAN => 'warning',
            self::SELESAI => 'success',
            self::DIBATALKAN => 'danger',
            default => 'gray',
        };
    }
}