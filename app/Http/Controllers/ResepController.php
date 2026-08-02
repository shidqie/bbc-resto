<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\ResepMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResepController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $menus = Menu::with('kategori_menu', 'jenis_menu')
            ->withCount('resep_menu')
            ->when($search, function ($q) use ($search) {
                $q->where('nama_menu', 'like', "%{$search}%")
                    ->orWhere('kode_menu', 'like', "%{$search}%");
            })
            ->orderBy('nama_menu', 'asc')
            ->paginate(10)->withQueryString();

        return view('menu.resep.index', compact('menus', 'search'));
    }

    public function create(Menu $menu)
    {
        $menu->load('resep_menu.bahan_baku.satuan');

        $bahanBakus = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();

        // Calculate current HPP
        $totalHpp = 0;
        foreach ($menu->resep_menu as $resep) {
            $hargaSatuan = $resep->bahan_baku->harga_satuan ?? 0;
            $totalHpp += $hargaSatuan * $resep->jumlah_kebutuhan;
        }

        return view('menu.resep.create', compact('menu', 'bahanBakus', 'totalHpp'));
    }

    public function store(Request $request, Menu $menu)
    {
        $request->validate([
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_baku,id',
            'jumlah_kebutuhan' => 'required|array',
            'jumlah_kebutuhan.*' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // Hapus resep lama
            $menu->resep_menu()->delete();

            // Insert resep baru
            if ($request->has('bahan_baku_id')) {
                foreach ($request->bahan_baku_id as $index => $bahanId) {
                    $bahan = BahanBaku::find($bahanId);
                    ResepMenu::create([
                        'menu_id' => $menu->id,
                        'bahan_baku_id' => $bahanId,
                        'jumlah_kebutuhan' => $request->jumlah_kebutuhan[$index],
                        'satuan_id' => $bahan->satuan_id ?? 1,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('resep.index')->with('success', "Resep untuk menu {$menu->nama_menu} berhasil diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat menyimpan resep: '.$e->getMessage())->withInput();
        }
    }
}
