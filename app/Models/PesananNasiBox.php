<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananNasiBox extends Model
{
    protected $fillable = [
        'kode_pesanan',
        'nama_pemesan',
        'kontak',
        'alamat',
        'tanggal_acara',
        'paket_id',
        'jumlah_box',
        'alamat',
        'metode_pengiriman',
        'ongkos_kirim',
        'jarak_km',
        'latitude',
        'longitude',
        'total_tagihan',
        'dp_amount',
        'status',
        'status_bayar',
        'catatan'
    ];

    protected $casts = [
        'tanggal_acara' => 'date',
    ];

    public function paket()
    {
        return $this->belongsTo(PaketCatering::class, 'paket_id');
    }

    public function statusLogs()
    {
        return $this->morphMany(PesananStatusLog::class, 'pesanan')->latest();
    }

    public function details()
    {
        return $this->hasMany(PesananNasiBoxDetail::class, 'pesanan_nasi_box_id');
    }

    public function buktiPembayarans()
    {
        return $this->morphMany(BuktiPembayaran::class, 'pesanan');
    }

    public static function generateKodePesanan(): string
    {
        $date = now()->format('Ymd');
        $lastOrder = self::where('kode_pesanan', 'like', 'NBX' . $date . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->kode_pesanan, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'NBX' . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
