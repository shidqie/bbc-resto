<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\KategoriMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with(['kategori', 'resep.bahanBaku']);

        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_menu_id', $request->kategori);
        }

        if ($request->has('jenis_menu') && $request->jenis_menu != '') {
            $query->where('jenis_menu', $request->jenis_menu);
        }

        $menus = $query->latest()->paginate(10)->withQueryString();
        $kategoris = KategoriMenu::orderBy('nama')->get();

        $stats = [
            'total' => Menu::count(),
            'dine_in' => Menu::where('jenis_menu', 'dine_in')->count(),
            'catering' => Menu::where('jenis_menu', 'catering')->count(),
            'nasi_box' => Menu::where('jenis_menu', 'nasi_box')->count(),
        ];

        return view('menu.index', compact('menus', 'kategoris', 'stats'));
    }

    public function create()
    {
        $kategoris = KategoriMenu::orderBy('nama')->get();
        return view('menu.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_menu_id' => 'required|exists:kategori_menus,id',
            'jenis_menu' => 'required|in:dine_in,catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:tersedia,habis',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('menu', 'public');
            $data['foto'] = $path;
        }

        Menu::create($data);

        return redirect()->route('menu.index')->with('success', 'Menu berhasil ditambahkan.');
    }

    public function edit(Menu $menu)
    {
        $kategoris = KategoriMenu::orderBy('nama')->get();
        return view('menu.edit', compact('menu', 'kategoris'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_menu_id' => 'required|exists:kategori_menus,id',
            'jenis_menu' => 'required|in:dine_in,catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:tersedia,habis',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            if ($menu->foto && Storage::disk('public')->exists($menu->foto)) {
                Storage::disk('public')->delete($menu->foto);
            }
            $path = $request->file('foto')->store('menu', 'public');
            $data['foto'] = $path;
        }

        $menu->update($data);

        return redirect()->route('menu.index')->with('success', 'Menu berhasil diperbarui.');
    }

    public function show(Menu $menu)
    {
        $menu->load(['kategori', 'resep.bahanBaku.satuan']);
        return view('menu.show', compact('menu'));
    }

    public function destroy(Menu $menu)
    {
        if ($menu->foto && Storage::disk('public')->exists($menu->foto)) {
            Storage::disk('public')->delete($menu->foto);
        }

        $menu->delete();

        return redirect()->route('menu.index')->with('success', 'Menu berhasil dihapus.');
    }
}
