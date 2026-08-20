<?php

namespace App\Models;

class DetailPurchaseOrder extends BaseModel
{
    protected $table = 'detail_purchase_order';

    protected $guarded = [];

    public function purchase_order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function detail_pengadaan_bahan()
    {
        return $this->belongsTo(DetailPengadaanBahan::class, 'detail_pengadaan_bahan_id');
    }

    public function bahan_baku()
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    /**
     * Sisa yang belum diterima untuk baris PO ini
     * (jumlah dipesan dikurangi total diterima pada seluruh penerimaan).
     */
    public function getSisaAttribute(): float
    {
        return max(0, (float) $this->jumlah_dipesan - (float) $this->jumlah_diterima);
    }

    public function getStatusNamaAttribute(): string
    {
        $dipesan = (float) $this->jumlah_dipesan;
        $diterima = (float) $this->jumlah_diterima;

        if ($diterima >= $dipesan && $dipesan > 0) {
            return 'Diterima';
        }
        if ($diterima > 0) {
            return 'Diterima Sebagian';
        }
        return 'Menunggu';
    }

    public function getStatusWarnaAttribute(): string
    {
        $nama = $this->status_nama;

        return match ($nama) {
            'Diterima' => 'success',
            'Diterima Sebagian' => 'warning',
            default => 'primary',
        };
    }

    public function getIdDetailPengadaanAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generateDetailPengadaanId($this->id);
    }
}