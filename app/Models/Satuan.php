<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    use HasFactory;

    protected $fillable = ['nama_satuan', 'singkatan'];

    public function bahanBakus()
    {
        return $this->hasMany(BahanBaku::class);
    }
}
