<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $fillable = [
        'no_pesanan',
        'nama_pelanggan',
        'no_meja',
        'jenis_pesanan',
        'tanggal_pesanan',
        'tanggal_pengiriman',
        'jumlah_porsi',
        'total_harga',
        'status_pembayaran',
        'status_pesanan',
        'keterangan',
        'user_id'
    ];

    protected $casts = [
        'tanggal_pesanan' => 'datetime',
        'tanggal_pengiriman' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(DetailPesanan::class);
    }

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class);
    }
}
