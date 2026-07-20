<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    protected $fillable = [
        'kode_pengadaan',
        'pesanan_catering_id',
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
}
