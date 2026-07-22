<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanBaku extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_bahan',
        'kategori_bahan_id',
        'supplier_id',
        'nama_bahan',
        'jenis_penggunaan',
        'satuan_id',
        'stok',
        'stok_minimum',
        'harga_terakhir',
        'lokasi_penyimpanan',
        'tanggal_kedaluwarsa',
        'keterangan',
        'status',
    ];

    public function kategoriBahan()
    {
        return $this->belongsTo(KategoriBahan::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function getJenisPenggunaanLabelAttribute()
    {
        return match($this->jenis_penggunaan) {
            'catering' => 'Catering',
            default => 'Resto & Nasi Box',
        };
    }
}
