<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    protected $fillable = [
        'kode_pengadaan',
        'pesanan_catering_id',
        'pesanan_nasi_box_id',
        'jenis_pesanan',
        'supplier_id',
        'tanggal_pengadaan',
        'total_biaya',
        'status',
        'catatan',
        'user_id'
    ];

    protected $casts = [
        'tanggal_pengadaan' => 'date'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(DetailPengadaan::class);
    }

    public function pesananCatering()
    {
        return $this->belongsTo(PesananCatering::class, 'pesanan_catering_id');
    }

    public function pesananNasiBox()
    {
        return $this->belongsTo(PesananNasiBox::class, 'pesanan_nasi_box_id');
    }

    public function getJenisLabelAttribute()
    {
        return match($this->jenis_pesanan) {
            'nasi_box' => 'Nasi Box',
            'umum' => 'Umum / Resto',
            default => 'Catering',
        };
    }
}
