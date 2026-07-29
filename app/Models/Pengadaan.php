<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengadaan extends Model
{
    protected $fillable = [
        'nomor_pengadaan',
        'asal_pembelian',
        'tanggal_pengadaan',
        'total_biaya',
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


}
