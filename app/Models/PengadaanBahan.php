<?php

namespace App\Models;

class PengadaanBahan extends BaseModel
{
    protected $table = 'pengadaan_bahan';
    protected $guarded = [];
    public $timestamps = false;

    public function pemasok() { return $this->belongsTo(Pemasok::class, 'pemasok_id'); }
    public function pesanan() { return $this->belongsTo(Pesanan::class, 'pesanan_id'); }
    public function diajukan_oleh_pengguna() { return $this->belongsTo(Pengguna::class, 'diajukan_oleh'); }
    public function disetujui_oleh_pengguna() { return $this->belongsTo(Pengguna::class, 'disetujui_oleh'); }
    public function status_pengadaan() { return $this->belongsTo(StatusPengadaan::class, 'status_pengadaan_id'); }
    public function detail_pengadaan_bahan() { return $this->hasMany(DetailPengadaanBahan::class, 'pengadaan_bahan_id'); }
    public function penerimaan_bahan() { return $this->hasManyThrough(PenerimaanBahan::class, PurchaseOrder::class, 'pengadaan_bahan_id', 'purchase_order_id'); }
    public function purchase_order() { return $this->hasMany(PurchaseOrder::class, 'pengadaan_bahan_id'); }

    /**
     * Nama status permintaan yang user-friendly.
     */
    public function getStatusNamaAttribute(): string
    {
        $kode = StatusPengadaan::kodeById($this->status_pengadaan_id);

        return match ($kode) {
            StatusPengadaan::MENUNGGU_PEMBELIAN => 'Menunggu PO',
            StatusPengadaan::DALAM_PROSES => 'Dalam Pengadaan',
            StatusPengadaan::MENUNGGU_PENERIMAAN => 'Dalam Pengadaan',
            StatusPengadaan::DITERIMA_SEBAGIAN => 'Terpenuhi Sebagian',
            StatusPengadaan::SELESAI => 'Selesai',
            StatusPengadaan::DIBATALKAN => 'Dibatalkan',
            default => $this->status_pengadaan->nama_status ?? 'Draft',
        };
    }

    /**
     * Warna badge status permintaan.
     */
    public function getStatusWarnaAttribute(): string
    {
        $kode = StatusPengadaan::kodeById($this->status_pengadaan_id);

        return match ($kode) {
            StatusPengadaan::MENUNGGU_PEMBELIAN => 'warning',
            StatusPengadaan::DALAM_PROSES => 'primary',
            StatusPengadaan::MENUNGGU_PENERIMAAN => 'primary',
            StatusPengadaan::DITERIMA_SEBAGIAN => 'warning',
            StatusPengadaan::SELESAI => 'success',
            StatusPengadaan::DIBATALKAN => 'danger',
            default => 'gray',
        };
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id_pengadaan)) {
                $latest = static::orderBy('id', 'desc')->first();
                $nextId = $latest ? $latest->id + 1 : 1;
                $prefix = 'PD';
                $model->id_pengadaan = $prefix . str_pad($nextId, 3, '0', STR_PAD_LEFT);
            }
        });
    }
}

