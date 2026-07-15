<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananCatering extends Model
{
    protected $fillable = [
        'kode_pesanan',
        'nama_pemesan',
        'kontak',
        'lokasi_acara',
        'metode_pengiriman',
        'ongkos_kirim',
        'jarak_km',
        'latitude',
        'longitude',
        'tanggal_acara',
        'paket_id',
        'jumlah_porsi',
        'total_tagihan',
        'dp_amount',
        'status',
        'catatan'
    ];

    protected $casts = [
        'tanggal_acara' => 'date',
    ];

    public function paket()
    {
        return $this->belongsTo(PaketCatering::class, 'paket_id');
    }

    public function details()
    {
        return $this->hasMany(PesananCateringDetail::class, 'pesanan_id');
    }

    public function addons()
    {
        return $this->hasMany(PesananCateringAddon::class, 'pesanan_id');
    }

    public function buktiPembayarans()
    {
        return $this->morphMany(BuktiPembayaran::class, 'pesanan');
    }

    public static function generateKodePesanan(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = self::where('kode_pesanan', 'like', 'CTR' . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->kode_pesanan, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'CTR' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
