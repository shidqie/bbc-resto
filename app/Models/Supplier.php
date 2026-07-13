<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = ['nama_supplier', 'kontak', 'alamat', 'keterangan'];

    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class);
    }
}
