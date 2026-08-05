<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Pengguna extends Authenticatable
{
    use Notifiable;

    protected $table = 'pengguna';

    protected $guarded = [];

    const CREATED_AT = 'dibuat_pada';

    const UPDATED_AT = 'diperbarui_pada';

    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    protected $casts = [
        'status_aktif' => 'boolean',
        'terakhir_masuk' => 'datetime',
        'dibuat_pada' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    public function peran()
    {
        return $this->belongsTo(Peran::class, 'peran_id');
    }

    public function pesananSebagaiPelayan()
    {
        return $this->hasMany(Pesanan::class, 'pelayan_id');
    }

    public function pesananSebagaiKasir()
    {
        return $this->hasMany(Pesanan::class, 'kasir_id');
    }

    public function pengadaanDiajukan()
    {
        return $this->hasMany(PengadaanBahan::class, 'diajukan_oleh');
    }

    public function pengadaanDisetujui()
    {
        return $this->hasMany(PengadaanBahan::class, 'disetujui_oleh');
    }

    public function penerimaanBahan()
    {
        return $this->hasMany(PenerimaanBahan::class, 'diterima_oleh');
    }

    public function mutasiStok()
    {
        return $this->hasMany(MutasiStok::class, 'dibuat_oleh');
    }

    public function penyesuaianStok()
    {
        return $this->hasMany(PenyesuaianStok::class, 'dibuat_oleh');
    }

    public function pengantaran()
    {
        return $this->hasMany(Pengantaran::class, 'ditugaskan_kepada');
    }

    public function pembayaranDiproses()
    {
        return $this->hasMany(Pembayaran::class, 'diproses_oleh');
    }

    public function isPemilik(): bool
    {
        return $this->peran && $this->peran->nama_peran === 'Pemilik';
    }

    public function isManajer(): bool
    {
        return $this->peran && $this->peran->nama_peran === 'Manajer';
    }

    public function isKasir(): bool
    {
        return $this->peran && $this->peran->nama_peran === 'Kasir';
    }

    public function isPelayan(): bool
    {
        return $this->peran && $this->peran->nama_peran === 'Pelayan';
    }

    public function isDapur(): bool
    {
        return $this->peran && $this->peran->nama_peran === 'Dapur';
    }

    public function isPengantaran(): bool
    {
        return $this->peran && $this->peran->nama_peran === 'Pengantaran';
    }

    public function isPelanggan(): bool
    {
        return $this->peran && $this->peran->nama_peran === 'Pelanggan';
    }

    public function pelanggan()
    {
        return $this->hasOne(Pelanggan::class, 'user_id');
    }

    public function isAdminSistem(): bool
    {
        return $this->peran && $this->peran->nama_peran === 'Admin Sistem';
    }

    public function isAdmin(): bool
    {
        return $this->peran && in_array($this->peran->nama_peran, ['Admin', 'Super Admin', 'Admin Sistem']);
    }
}
