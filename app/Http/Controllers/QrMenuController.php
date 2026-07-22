<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QrMenuController extends Controller
{
    public function index()
    {
        $kategoris = \App\Models\KategoriMenu::all();
        $menus = \App\Models\Menu::with('kategori')->where('status', 'tersedia')->get();
        return view('qr-menu.index', compact('kategoris', 'menus'));
    }
}
