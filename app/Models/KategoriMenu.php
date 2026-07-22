<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriMenu extends Model
{
    protected $table = 'kategori_menus';

    protected $fillable = [
        'kode_kategori',
        'nama',
        'jenis_menu'
    ];

    public function menus()
    {
        return $this->hasMany(Menu::class);
    }

    public static function generateKodeKategori()
    {
        $last = self::whereNotNull('kode_kategori')->orderBy('id', 'desc')->first();
        if (!$last || !$last->kode_kategori) {
            return 'KTG-01';
        }
        $number = (int) str_replace('KTG-', '', $last->kode_kategori);
        $next = $number + 1;
        return 'KTG-' . str_pad($next, 2, '0', STR_PAD_LEFT);
    }

    public function getJenisLabelAttribute()
    {
        return match($this->jenis_menu) {
            'catering' => 'Catering',
            'nasi_box' => 'Nasi Box',
            default => 'Resto',
        };
    }
}
