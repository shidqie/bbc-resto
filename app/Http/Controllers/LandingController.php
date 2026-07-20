<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\PaketCatering;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        // 1. Ambil Menu Dine-in beserta Kategori
        // Hanya ambil kategori yang memiliki menu dine-in aktif
        $kategoris = KategoriMenu::with(['menus' => function($query) {
            $query->where('status', 'tersedia')->where('jenis_menu', 'dine_in');
        }])->whereHas('menus', function($query) {
            $query->where('status', 'tersedia')->where('jenis_menu', 'dine_in');
        })->get();

        // 2. Ambil Semua Menu Dine-In aktif (untuk opsi tab "Semua")
        $semuaMenu = Menu::where('status', 'tersedia')->where('jenis_menu', 'dine_in')->get();

        // 3. Ambil Paket Catering aktif
        $paketCatering = PaketCatering::with('komponens.opsi.menu')
            ->where('is_active', true)
            ->where('jenis_paket', 'catering')
            ->get();

        $paketNasiBox = PaketCatering::with('komponens.opsi.menu')
            ->where('is_active', true)
            ->where('jenis_paket', 'nasi_box')
            ->get();

        return view('landing', compact('kategoris', 'semuaMenu', 'paketCatering', 'paketNasiBox'));
    }
}
