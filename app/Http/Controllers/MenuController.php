<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\KategoriMenu;
use App\Models\BahanBaku;
use App\Models\ResepMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        // Auto-fill kode_menu untuk data lama yang belum punya kode
        $missingCodes = Menu::whereNull('kode_menu')->orderBy('id', 'asc')->get();
        foreach ($missingCodes as $m) {
            $m->kode_menu = Menu::generateKodeMenu();
            $m->save();
        }

        $query = Menu::with(['kategori', 'resep.bahanBaku.satuan']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_menu', 'like', "%{$search}%");
            });
        }

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_menu_id', $request->kategori);
        }

        if ($request->has('jenis_menu') && $request->jenis_menu != '') {
            $query->where('jenis_menu', $request->jenis_menu);
        }

        $menus = $query->orderBy('id', 'asc')->paginate(12)->withQueryString();
        $kategoris = KategoriMenu::orderBy('id', 'asc')->get();
        $bahanBakus = BahanBaku::with('satuan')->where('status', 1)->orderBy('nama_bahan')->get();

        $stats = [
            'total' => Menu::count(),
            'dine_in' => Menu::where('jenis_menu', 'dine_in')->count(),
            'catering' => Menu::where('jenis_menu', 'catering')->count(),
            'nasi_box' => Menu::where('jenis_menu', 'nasi_box')->count(),
        ];

        return view('menu.index', compact('menus', 'kategoris', 'bahanBakus', 'stats'));
    }

    public function create()
    {
        $kategoris = KategoriMenu::orderBy('nama')->get();
        $bahanBakus = BahanBaku::with('satuan')->where('status', 1)->orderBy('nama_bahan')->get();
        return view('menu.create', compact('kategoris', 'bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_menu_id' => 'required|exists:kategori_menus,id',
            'jenis_menu' => 'required|in:dine_in,catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:tersedia,nonaktif,habis',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'bahan_baku_id' => 'nullable|array',
            'jumlah_kebutuhan' => 'nullable|array',
        ]);

        $data = $request->except(['foto', 'bahan_baku_id', 'jumlah_kebutuhan']);
        $data['kode_menu'] = Menu::generateKodeMenu();

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('menu', 'public');
            $data['foto'] = $path;
        }

        $menu = Menu::create($data);

        // Process BOM (Komposisi Bahan Baku)
        $hasBom = false;
        if ($request->has('bahan_baku_id') && is_array($request->bahan_baku_id)) {
            foreach ($request->bahan_baku_id as $idx => $bahanId) {
                $qty = $request->jumlah_kebutuhan[$idx] ?? 0;
                if ($bahanId && $qty > 0) {
                    $bahan = BahanBaku::find($bahanId);
                    ResepMenu::create([
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah_kebutuhan' => $qty,
                        'satuan' => $bahan->satuan->nama_satuan ?? 'porsi',
                    ]);
                    $hasBom = true;
                }
            }
        }

        // Skenario Alternatif A3: Peringatan jika menyimpan menu tanpa mengisi BOM
        if (!$hasBom) {
            return redirect()->route('menu.index')->with([
                'success' => "Menu {$menu->kode_menu} - {$menu->nama} berhasil disimpan.",
                'warning_bom' => "Peringatan (Skenario A3): Menu '{$menu->nama}' disimpan tanpa data BOM (Komposisi Bahan Baku). Perhitungan otomatis kebutuhan bahan baku saat pesanan masuk tidak akan berjalan untuk menu ini sampai data BOM dilengkapi."
            ]);
        }

        return redirect()->route('menu.index')->with('success', "Menu {$menu->kode_menu} - {$menu->nama} beserta komposisi BOM berhasil disimpan.");
    }

    public function edit(Menu $menu)
    {
        $menu->load('resep.bahanBaku.satuan');
        $kategoris = KategoriMenu::orderBy('nama')->get();
        $bahanBakus = BahanBaku::with('satuan')->where('status', 1)->orderBy('nama_bahan')->get();
        return view('menu.edit', compact('menu', 'kategoris', 'bahanBakus'));
    }

    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kategori_menu_id' => 'required|exists:kategori_menus,id',
            'jenis_menu' => 'required|in:dine_in,catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:tersedia,nonaktif,habis',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'bahan_baku_id' => 'nullable|array',
            'jumlah_kebutuhan' => 'nullable|array',
        ]);

        $data = $request->except(['foto', 'bahan_baku_id', 'jumlah_kebutuhan']);

        if ($request->hasFile('foto')) {
            if ($menu->foto && Storage::disk('public')->exists($menu->foto)) {
                Storage::disk('public')->delete($menu->foto);
            }
            $path = $request->file('foto')->store('menu', 'public');
            $data['foto'] = $path;
        }

        $menu->update($data);

        // Re-sync BOM
        $menu->resep()->delete();
        $hasBom = false;

        if ($request->has('bahan_baku_id') && is_array($request->bahan_baku_id)) {
            foreach ($request->bahan_baku_id as $idx => $bahanId) {
                $qty = $request->jumlah_kebutuhan[$idx] ?? 0;
                if ($bahanId && $qty > 0) {
                    $bahan = BahanBaku::find($bahanId);
                    ResepMenu::create([
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah_kebutuhan' => $qty,
                        'satuan' => $bahan->satuan->nama_satuan ?? 'porsi',
                    ]);
                    $hasBom = true;
                }
            }
        }

        if (!$hasBom) {
            return redirect()->route('menu.index')->with([
                'success' => "Menu {$menu->kode_menu} berhasil diperbarui.",
                'warning_bom' => "Peringatan (Skenario A3): Menu '{$menu->nama}' tidak memiliki data BOM (Komposisi Bahan Baku). Perhitungan otomatis kebutuhan bahan baku tidak akan berjalan untuk menu ini."
            ]);
        }

        return redirect()->route('menu.index')->with('success', "Menu {$menu->kode_menu} - {$menu->nama} beserta komposisi BOM berhasil diperbarui.");
    }

    public function show(Menu $menu)
    {
        $menu->load(['kategori', 'resep.bahanBaku.satuan']);
        return view('menu.show', compact('menu'));
    }

    // Skenario Alternatif A2: Menonaktifkan menu (bukan menghapus)
    public function toggleStatus(Menu $menu)
    {
        $newStatus = ($menu->status === 'nonaktif') ? 'tersedia' : 'nonaktif';
        $menu->update(['status' => $newStatus]);

        $statusText = ($newStatus === 'nonaktif') ? 'Dinonaktifkan' : 'Diaktifkan';
        return redirect()->route('menu.index')->with('success', "Status menu '{$menu->nama}' ({$menu->kode_menu}) berhasil diubah menjadi {$statusText}.");
    }

    public function destroy(Menu $menu)
    {
        if ($menu->foto && Storage::disk('public')->exists($menu->foto)) {
            Storage::disk('public')->delete($menu->foto);
        }

        $menu->resep()->delete();
        $menu->delete();

        return redirect()->route('menu.index')->with('success', 'Menu berhasil dihapus.');
    }
}
