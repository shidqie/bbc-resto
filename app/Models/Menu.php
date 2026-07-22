<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'kode_menu',
        'kategori_menu_id',
        'nama',
        'jenis_menu',
        'deskripsi',
        'harga',
        'foto',
        'status'
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_menu_id');
    }

    public function resep()
    {
        return $this->hasMany(ResepMenu::class, 'menu_id');
    }

    public static function generateKodeMenu()
    {
        $last = self::whereNotNull('kode_menu')->orderBy('id', 'desc')->first();
        if (!$last || !$last->kode_menu) {
            return 'MNU-01';
        }
        $number = (int) str_replace('MNU-', '', $last->kode_menu);
        $next = $number + 1;
        return 'MNU-' . str_pad($next, 2, '0', STR_PAD_LEFT);
    }

    public function getJenisLabelAttribute()
    {
        return match($this->jenis_menu) {
            'catering' => 'Catering',
            'nasi_box' => 'Nasi Box',
            default => 'Resto',
        };
    }

    /**
     * Cek apakah menu habis berdasarkan status atau ketersediaan stok bahan baku (BOM)
     */
    public function isHabis()
    {
        if ($this->status === 'nonaktif' || $this->status === 'habis') {
            return true;
        }

        if ($this->resep && $this->resep->count() > 0) {
            foreach ($this->resep as $r) {
                if ($r->bahanBaku && $r->bahanBaku->stok < $r->jumlah_kebutuhan) {
                    return true;
                }
            }
        }

        return false;
    }
}
