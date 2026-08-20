<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\Galeri;

class LandingController extends Controller
{
    public function index()
    {
        // 1. Ambil Menu Dine-in beserta Kategori
        // Hanya ambil kategori yang memiliki menu dine-in aktif atau yang belum memiliki jenis (null)
        $kategoris = KategoriMenu::with(['menu' => function ($query) {
            $query->where('status_aktif', true)
                ->where(function ($q) {
                    $q->where('jenis_menu_id', 1)->orWhereNull('jenis_menu_id');
                });
        }])->whereHas('menu', function ($query) {
            $query->where('status_aktif', true)
                ->where(function ($q) {
                    $q->where('jenis_menu_id', 1)->orWhereNull('jenis_menu_id');
                });
        })->get();

        // 2. Ambil Semua Menu Dine-In aktif (untuk opsi tab "Semua")
        $semuaMenu = Menu::where('status_aktif', true)
            ->where(function ($q) {
                $q->where('jenis_menu_id', 1)->orWhereNull('jenis_menu_id');
            })->get();

        // 3. Ambil Paket Catering aktif (jenis_menu_id = 2)
        $paketCatering = Menu::where('status_aktif', true)->where('jenis_menu_id', 2)->whereHas('komponen_paket')->with('komponen_paket.opsi')->get();

        // 4. Ambil Paket Nasi Box aktif (jenis_menu_id = 3)
        $paketNasiBox = Menu::where('status_aktif', true)->where('jenis_menu_id', 3)->whereHas('komponen_paket')->with('komponen_paket.opsi')->get();

        // 5. Ambil Galeri
        $galeri = Galeri::where('is_active', true)->orderBy('urutan', 'asc')->orderBy('dibuat_pada', 'desc')->get();

        return view('landing', compact('kategoris', 'semuaMenu', 'paketCatering', 'paketNasiBox', 'galeri'));
    }
}
