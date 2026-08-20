<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Galeri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GaleriController extends Controller
{
    public function index()
    {
        $galeri = Galeri::orderBy('urutan', 'asc')->orderBy('dibuat_pada', 'desc')->get();
        return view('admin.pengaturan.galeri.index', compact('galeri'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $path = $request->file('foto')->store('galeri', 'public');

        Galeri::create([
            'foto' => $path,
            'is_active' => true,
            'urutan' => Galeri::max('urutan') + 1,
        ]);

        return redirect()->route('admin.pengaturan.galeri.index')->with('success', 'Foto galeri berhasil ditambahkan.');
    }

    public function destroy(Galeri $galeri)
    {
        if ($galeri->foto && Storage::disk('public')->exists($galeri->foto)) {
            Storage::disk('public')->delete($galeri->foto);
        }

        $galeri->delete();

        return redirect()->route('admin.pengaturan.galeri.index')->with('success', 'Foto galeri berhasil dihapus.');
    }
}
