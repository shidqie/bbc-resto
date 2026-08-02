<?php

namespace App\Http\Controllers;

use App\Models\KategoriMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KategoriMenuController extends Controller
{
    public function index(Request $request)
    {
        $query = KategoriMenu::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kategori', 'like', "%{$search}%");
            });
        }

        $kategoris = $query->withCount('menu')->orderBy('id', 'asc')->paginate(15)->withQueryString();

        return view('menu.kategori.index', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $namaKat = $request->input('nama_kategori') ?? $request->input('nama');
        $request->merge(['nama_kategori' => $namaKat]);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_menu,nama_kategori',
            'deskripsi' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $namaKat) {
            $data = [
                'nama_kategori' => trim($namaKat),
                'deskripsi' => $request->deskripsi,
            ];
            if (Schema::hasColumn('kategori_menu', 'status_aktif')) {
                $data['status_aktif'] = true;
            }
            KategoriMenu::create($data);

            return redirect()->route('kategori-menu.index')->with('success', "Kategori menu '{$namaKat}' berhasil ditambahkan.");
        });
    }

    public function update(Request $request, KategoriMenu $kategori_menu)
    {
        $namaKat = $request->input('nama_kategori') ?? $request->input('nama');
        $request->merge(['nama_kategori' => $namaKat]);

        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_menu,nama_kategori,'.$kategori_menu->id,
            'deskripsi' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $kategori_menu, $namaKat) {
            $kategori_menu->update([
                'nama_kategori' => trim($namaKat),
                'deskripsi' => $request->deskripsi,
            ]);

            return redirect()->route('kategori-menu.index')->with('success', "Kategori menu '{$namaKat}' berhasil diperbarui.");
        });
    }

    public function toggleStatus(KategoriMenu $kategori_menu)
    {
        $kategori_menu->update(['status_aktif' => ! $kategori_menu->status_aktif]);

        $status = $kategori_menu->status_aktif ? 'diaktifkan' : 'dinonaktifkan';

        return redirect()->route('kategori-menu.index')
            ->with('success', "Kategori '{$kategori_menu->nama_kategori}' berhasil {$status}.");
    }

    public function destroy(KategoriMenu $kategori_menu)
    {
        return DB::transaction(function () use ($kategori_menu) {
            $count = $kategori_menu->menu()->count();
            if ($count > 0) {
                return redirect()->route('kategori-menu.index')
                    ->with('error', "Gagal Menghapus: Kategori '{$kategori_menu->nama_kategori}' tidak dapat dihapus karena masih digunakan oleh {$count} menu.");
            }

            $nama = $kategori_menu->nama_kategori;
            $kategori_menu->delete();

            return redirect()->route('kategori-menu.index')->with('success', "Kategori menu '{$nama}' berhasil dihapus.");
        });
    }
}
