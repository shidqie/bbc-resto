<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use Illuminate\Http\Request;

class KategoriMenuController extends Controller
{
    public function index(Request $request)
    {
        // Auto-fill kode_kategori untuk data lama yang belum punya kode
        $missingCodes = KategoriMenu::whereNull('kode_kategori')->orderBy('id', 'asc')->get();
        foreach ($missingCodes as $k) {
            $k->kode_kategori = KategoriMenu::generateKodeKategori();
            $k->save();
        }

        $query = KategoriMenu::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_kategori', 'like', "%{$search}%");
            });
        }

        if ($request->has('jenis') && $request->jenis != '') {
            $query->where('jenis_menu', $request->jenis);
        }

        $kategoris = $query->withCount('menus')->orderBy('id', 'asc')->paginate(15)->withQueryString();

        return view('kategori-menu.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_menu' => 'required|in:dine_in,catering,nasi_box'
        ]);

        // Skenario Alternatif A1: Validasi duplikasi nama kategori pada jenis menu yang sama
        $exists = KategoriMenu::where('nama', trim($request->nama))
            ->where('jenis_menu', $request->jenis_menu)
            ->exists();

        if ($exists) {
            $jenisLabel = match($request->jenis_menu) {
                'catering' => 'Catering',
                'nasi_box' => 'Nasi Box',
                default => 'Resto',
            };
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal Menyimpan: Nama kategori '{$request->nama}' dengan jenis menu '{$jenisLabel}' sudah terdaftar! Harap gunakan nama lain.");
        }

        $kodeKategori = KategoriMenu::generateKodeKategori();

        KategoriMenu::create([
            'kode_kategori' => $kodeKategori,
            'nama' => trim($request->nama),
            'jenis_menu' => $request->jenis_menu,
        ]);

        return redirect()->route('kategori-menu.index')->with('success', 'Kategori menu ' . $kodeKategori . ' (' . $request->nama . ') berhasil ditambahkan.');
    }

    public function update(Request $request, KategoriMenu $kategori_menu)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_menu' => 'required|in:dine_in,catering,nasi_box'
        ]);

        // Skenario Alternatif A1: Validasi duplikasi saat update
        $exists = KategoriMenu::where('nama', trim($request->nama))
            ->where('jenis_menu', $request->jenis_menu)
            ->where('id', '!=', $kategori_menu->id)
            ->exists();

        if ($exists) {
            $jenisLabel = match($request->jenis_menu) {
                'catering' => 'Catering',
                'nasi_box' => 'Nasi Box',
                default => 'Resto',
            };
            return redirect()->back()
                ->withInput()
                ->with('error', "Gagal Perbarui: Nama kategori '{$request->nama}' dengan jenis menu '{$jenisLabel}' sudah terdaftar! Harap gunakan nama lain.");
        }

        $kategori_menu->update([
            'nama' => trim($request->nama),
            'jenis_menu' => $request->jenis_menu,
        ]);

        return redirect()->route('kategori-menu.index')->with('success', 'Kategori menu ' . $kategori_menu->kode_kategori . ' berhasil diperbarui.');
    }

    public function destroy(KategoriMenu $kategori_menu)
    {
        // Skenario Alternatif A2: Menghapus kategori yang masih dipakai menu
        $count = $kategori_menu->menus()->count();
        if ($count > 0) {
            return redirect()->route('kategori-menu.index')
                ->with('error', "Gagal Menghapus: Kategori '{$kategori_menu->nama}' tidak dapat dihapus karena masih digunakan oleh {$count} data menu. Harap pindahkan menu ke kategori lain terlebih dahulu.");
        }

        $nama = $kategori_menu->nama;
        $kode = $kategori_menu->kode_kategori;
        $kategori_menu->delete();

        return redirect()->route('kategori-menu.index')->with('success', "Kategori menu '{$kode} - {$nama}' berhasil dihapus.");
    }
}
