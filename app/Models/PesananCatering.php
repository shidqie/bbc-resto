<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesananCatering extends Model
{
    protected $fillable = [
        'no_pesanan',
        'paket_catering_id',
        'nama_pemesan',
        'no_telepon',
        'email',
        'alamat_pengiriman',
        'tanggal_acara',
        'detail_acara',
        'jumlah_porsi',
        'harga_per_porsi',
        'total_harga',
        'dp_amount',
        'dp_percentage',
        'sisa_pembayaran',
        'status',
        'confirmed_by',
        'confirmed_at',
        'catatan_pembatalan',
    ];

    protected $casts = [
        'tanggal_acara' => 'date',
        'confirmed_at' => 'datetime',
    ];

    public function paketCatering()
    {
        return $this->belongsTo(PaketCatering::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function pembayarans()
    {
        return $this->hasMany(PembayaranCatering::class);
    }

    public function detailBahan()
    {
        return $this->hasMany(DetailPesananCatering::class);
    }

    /**
     * Generate nomor pesanan otomatis
     */
    public static function generateNoPesanan(string $jenisPaket): string
    {
        $prefix = $jenisPaket === 'catering' ? 'CTR' : 'NBX';
        $date = now()->format('Ymd');
        $lastOrder = self::where('no_pesanan', 'like', $prefix . $date . '%')
            ->orderBy('no_pesanan', 'desc')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) substr($lastOrder->no_pesanan, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . $date . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }
}
