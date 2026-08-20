<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AturanPengiriman extends Model
{
    use HasFactory;

    protected $table = 'aturan_pengiriman';

    protected $fillable = [
        'minimal_porsi',
        'maksimal_porsi',
        'kilometer_gratis',
        'status_aktif',
    ];

    protected $casts = [
        'minimal_porsi' => 'integer',
        'maksimal_porsi' => 'integer',
        'kilometer_gratis' => 'float',
        'status_aktif' => 'boolean',
    ];
}
