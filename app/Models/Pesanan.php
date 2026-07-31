<?php

namespace App\Models;

class Pesanan extends BaseModel
{
    protected $table = 'pesanan';
    protected $guarded = [];

    public function jenis_pesanan() { return $this->belongsTo(JenisPesanan::class, 'jenis_pesanan_id'); }

    public function pelanggan() { return $this->belongsTo(Pelanggan::class, 'pelanggan_id'); }

    public function meja() { return $this->belongsTo(Meja::class, 'meja_id'); }

    public function pelayan() { return $this->belongsTo(Pengguna::class, 'pelayan_id'); }

    public function kasir() { return $this->belongsTo(Pengguna::class, 'kasir_id'); }

    public function status_pesanan() { return $this->belongsTo(StatusPesanan::class, 'status_pesanan_id'); }

    public function detail_pesanan() { return $this->hasMany(DetailPesanan::class, 'pesanan_id'); }

    public function pembayaran() { return $this->hasMany(Pembayaran::class, 'pesanan_id'); }

    public function jadwal_pesanan() { return $this->hasOne(JadwalPesanan::class, 'pesanan_id'); }

    public function tiket_dapur() { return $this->hasMany(TiketDapur::class, 'pesanan_id'); }

    public function pengantaran() { return $this->hasOne(Pengantaran::class, 'pesanan_id'); }

    public function pengadaan_bahan() { return $this->hasMany(PengadaanBahan::class, 'pesanan_id'); }

    public function stok_catering() { return $this->hasMany(StokCatering::class, 'pesanan_id'); }
}
