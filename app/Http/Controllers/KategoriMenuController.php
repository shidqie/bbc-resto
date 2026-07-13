<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use Illuminate\Http\Request;

class KategoriMenuController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriMenu::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        $kategoris = $query->withCount('menus')->paginate(10)->withQueryString();

        return view('kategori-menu.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_menus,nama'
        ]);

        KategoriMenu::create($request->only('nama'));

        return redirect()->route('kategori-menu.index')->with('success', 'Kategori menu berhasil ditambahkan.');
    }

    public function update(Request $request, KategoriMenu $kategori_menu)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_menus,nama,' . $kategori_menu->id
        ]);

        $kategori_menu->update($request->only('nama'));

        return redirect()->route('kategori-menu.index')->with('success', 'Kategori menu berhasil diperbarui.');
    }

    public function destroy(KategoriMenu $kategori_menu)
    {
        if ($kategori_menu->menus()->count() > 0) {
            return redirect()->route('kategori-menu.index')->with('error', 'Kategori tidak dapat dihapus karena masih memiliki menu terkait.');
        }

        $kategori_menu->delete();

        return redirect()->route('kategori-menu.index')->with('success', 'Kategori menu berhasil dihapus.');
    }
}
