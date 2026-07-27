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

    protected $appends = [
        'is_habis'
    ];

    public function getIsHabisAttribute()
    {
        return $this->isHabis();
    }

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
     * Cek ketersediaan bahan baku (BOM) untuk menu ini
     *
     * @param int $menuId
     * @param int $jumlahPesan
     * @return bool
     */
    public static function cekKetersediaanBahan($menuId, $jumlahPesan = 1)
    {
        return \App\Services\BOMService::cekKetersediaanBahan($menuId, $jumlahPesan);
    }

    /**
     * Kurangi stok bahan baku (BOM) secara otomatis dan atomik
     *
     * @param int $menuId
     * @param int $jumlahPesan
     * @param int|null $pesananId
     * @return bool
     */
    public static function kurangiStokBahan($menuId, $jumlahPesan = 1, $pesananId = null)
    {
        return \App\Services\BOMService::kurangiStokBahan($menuId, $jumlahPesan, $pesananId);
    }

    /**
     * Cek apakah menu habis berdasarkan status atau ketersediaan stok bahan baku (BOM)
     */
    public function isHabis()
    {
        return !self::cekKetersediaanBahan($this->id, 1);
    }
}
