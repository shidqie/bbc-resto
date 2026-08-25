<?php

namespace App\Models;

use App\Models\PaymentSession;

class Pesanan extends BaseModel
{
    protected $table = 'pesanan';

    protected $guarded = [];

    public function jenis_pesanan()
    {
        return $this->belongsTo(JenisPesanan::class, 'jenis_pesanan_id');
    }

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function meja()
    {
        return $this->belongsTo(Meja::class, 'meja_id');
    }

    public function pelayan()
    {
        return $this->belongsTo(Pengguna::class, 'pelayan_id');
    }

    public function kasir()
    {
        return $this->belongsTo(Pengguna::class, 'kasir_id');
    }

    public function status_pesanan()
    {
        return $this->belongsTo(StatusPesanan::class, 'status_pesanan_id');
    }

    public function status_pembayaran()
    {
        return $this->belongsTo(StatusPembayaran::class, 'status_pembayaran_id');
    }

    public function detail_pesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'pesanan_id');
    }

    public function jadwal_pesanan()
    {
        return $this->hasOne(JadwalPesanan::class, 'pesanan_id');
    }

    public function tiket_dapur()
    {
        return $this->hasMany(TiketDapur::class, 'pesanan_id');
    }

    public function pengiriman()
    {
        return $this->hasOne(Pengiriman::class, 'pesanan_id');
    }

    public function pengadaan_bahan()
    {
        return $this->hasMany(PengadaanBahan::class, 'pesanan_id');
    }

    public function stok_catering()
    {
        return $this->hasMany(StokCatering::class, 'pesanan_id');
    }

    public function payment_sessions()
    {
        return $this->hasMany(PaymentSession::class, 'pesanan_id');
    }

    /**
     * Cek apakah pesanan ini memilih pembayaran langsung lunas (100%) tanpa skema DP.
     */
    public function isSkemaLunas(): bool
    {
        if ((int) $this->jenis_pesanan_id === 1) {
            return true;
        }

        // Cek riwayat pembayaran awal atau sesi pembayaran yang dibuat
        $pembayaranFirst = $this->pembayaran()->orderBy('id', 'asc')->first();
        if ($pembayaranFirst) {
            if (in_array($pembayaranFirst->jenis_pembayaran, ['pelunasan', 'pembayaran_penuh'])) {
                return true;
            }
            if ((float) $pembayaranFirst->jumlah_tagihan >= (float) $this->total_tagihan) {
                return true;
            }
        }

        return false;
    }

    /**
     * Persentase uang muka (DP) berdasarkan jenis pesanan:
     * Dine In = bayar penuh (100%), Catering = 50%, Nasi Box = 25%.
     */
    public function persentaseDP(): int
    {
        if ($this->isSkemaLunas()) {
            return 100;
        }

        return match ((int) $this->jenis_pesanan_id) {
            1 => 100, // Dine In / Takeaway
            3 => 25,  // Nasi Box
            default => 50, // Catering
        };
    }

    /**
     * Besaran uang muka (DP) dalam rupiah
     */
    public function nominalDP(): float
    {
        if ($this->isSkemaLunas()) {
            return (float) $this->total_tagihan;
        }

        return (float) $this->total_tagihan * ($this->persentaseDP() / 100);
    }

    public function getKodePesananAttribute()
    {
        return $this->id_pesanan;
    }

    public function getNamaKonsumenAttribute(): string
    {
        if ($this->pelanggan && !empty($this->pelanggan->nama)) {
            return $this->pelanggan->nama;
        }
        if (!empty($this->catatan)) {
            $raw = $this->catatan;
            if (preg_match('/Self-Order QR \(([^)]+)\)/iu', $raw, $m)) {
                return trim($m[1]);
            }
            if (str_contains($raw, ' | ')) {
                $raw = explode(' | ', $raw)[0];
            }
            if (preg_match('/^(.*?)\s*(\x{2013}|\x{2014}|–|-)\s*[0-9\+]{6,15}/u', $raw, $m)) {
                $raw = $m[1];
            }
            $raw = preg_replace('/\s*\(\d+\s*tamu\)/iu', '', $raw);
            if (preg_match('/^Pemesan:\s*(.+)$/iu', $raw, $m)) {
                return trim($m[1]);
            }
            $raw = trim($raw);
            if (strlen($raw) > 0 && !str_starts_with($raw, 'Self-Order')) {
                return $raw;
            }
        }
        return 'Tamu';
    }

    public function getNoTeleponAttribute(): string
    {
        if ($this->pelanggan && !empty($this->pelanggan->nomor_telepon)) {
            return $this->pelanggan->nomor_telepon;
        }
        if (!empty($this->catatan)) {
            if (preg_match('/\|\s*([0-9\+\-\s]{6,})/u', $this->catatan, $m)) {
                return trim($m[1]);
            }
            if (preg_match('/(?:\x{2013}|\x{2014}|–|-)\s*([0-9\+]{6,15})/u', $this->catatan, $m)) {
                return trim($m[1]);
            }
        }
        return '-';
    }

    public function getSumberPesananAttribute(): string
    {
        return str_contains($this->catatan ?? '', 'Self-Order') ? 'self_order' : 'pos';
    }

    public function getMetodePemesananAttribute(): string
    {
        return $this->sumber_pesanan === 'self_order' ? 'Self-order' : 'Pemesanan via Kasir';
    }

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id_pesanan)) {
                $model->id_pesanan = \App\Helpers\IdCodeGenerator::generatePesananId($model->tanggal_pesanan ?? $model->dibuat_pada ?? now());
            }
        });
    }
}

