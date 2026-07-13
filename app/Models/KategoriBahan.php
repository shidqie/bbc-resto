<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriBahan extends Model
{
    use HasFactory;

    protected $fillable = ['nama_kategori', 'deskripsi'];

    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class);
    }
}
