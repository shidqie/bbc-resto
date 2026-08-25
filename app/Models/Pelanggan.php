<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Notifications\Notifiable;

class Pelanggan extends BaseModel implements AuthenticatableContract, CanResetPasswordContract
{
    use Authenticatable, CanResetPassword, Notifiable;

    protected $table = 'pelanggan';

    protected $guarded = [];

    protected $hidden = ['kata_sandi', 'remember_token'];

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    public function pesanan()
    {
        return $this->hasMany(Pesanan::class, 'pelanggan_id');
    }

    public function pengguna()
    {
        return $this->belongsTo(Pengguna::class, 'user_id');
    }

    public function getKodePelangganAttribute(): string
    {
        return \App\Helpers\IdCodeGenerator::generatePelangganId($this->id);
    }

    public function getIdPelangganAttribute(): string
    {
        return $this->kode_pelanggan;
    }

    public function getStatusAkunAttribute(): string
    {
        if (!empty($this->kata_sandi)) {
            return 'Terdaftar';
        }

        if ($this->user_id && $this->pengguna) {
            return 'Terdaftar';
        }

        $existsInPengguna = \App\Models\Pengguna::where(function($q) {
            if (!empty($this->email)) $q->where('email', $this->email);
            if (!empty($this->nomor_telepon)) $q->orWhere('nomor_telepon', $this->nomor_telepon);
        })->exists();

        return $existsInPengguna ? 'Terdaftar' : 'Tamu';
    }
}
