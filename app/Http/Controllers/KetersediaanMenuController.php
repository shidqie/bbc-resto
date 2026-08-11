<?php

namespace App\Http\Controllers;

use App\Models\JenisMenu;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\StokBahan;
use App\Services\KebutuhanBahanService;
use Illuminate\Http\Request;

/**
 * Ketersediaan Menu (FR-12).
 *
 * Ketersediaan dihitung per layanan berdasarkan saldo jenis persediaan:
 *  - Dine-In dan Nasi Box  → Stok Bahan Baku Harian.
 *  - Paket Catering        → Stok Bahan Baku Catering.
 *
 * Status aktif menu berbeda dengan status ketersediaan menu.
 */
class KetersediaanMenuController extends Controller
{
    public function __construct(protected KebutuhanBahanService $kebutuhanBahanService)
    {
    }

    public function index(Request $request)
    {
        $query = Menu::with(['kategori_menu', 'resep_menu.bahan_baku'])
            ->where('status_aktif', true);

        if ($request->filled('search')) {
            $query->where('nama_menu', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('kategori')) {
            $query->where('kategori_menu_id', $request->kategori);
        }

        if ($request->filled('jenis_menu')) {
            $query->where('jenis_menu_id', $request->jenis_menu);
        }

        $menus = $query->orderBy('nama_menu')->paginate(15)->withQueryString();

        $menus->getCollection()->transform(function ($menu) {
            $menu->porsi_harian = $this->porsiPada($menu, StokBahan::JENIS_HARIAN);
            $menu->porsi_catering = $this->porsiPada($menu, StokBahan::JENIS_CATERING);
            $menu->status_harian = $this->statusKetersediaan($menu, $menu->porsi_harian);
            $menu->status_catering = $this->statusKetersediaan($menu, $menu->porsi_catering);

            return $menu;
        });

        $kategoris = KategoriMenu::orderBy('nama_kategori')->get();
        $jenisMenus = JenisMenu::all();

        $stats = [
            'total' => $menus->total(),
            'tersedia_harian' => $menus->getCollection()->filter(fn ($m) => $m->status_harian === 'Tersedia')->count(),
            'tidak_cukup_harian' => $menus->getCollection()->filter(fn ($m) => $m->status_harian === 'Stok Tidak Cukup')->count(),
            'tersedia_catering' => $menus->getCollection()->filter(fn ($m) => $m->status_catering === 'Tersedia')->count(),
            'tidak_cukup_catering' => $menus->getCollection()->filter(fn ($m) => $m->status_catering === 'Stok Tidak Cukup')->count(),
        ];

        return view('admin.persediaan.ketersediaan-menu.index', compact('menus', 'kategoris', 'jenisMenus', 'stats'));
    }

    protected function porsiPada(Menu $menu, string $jenisPersediaan): float
    {
        return $this->kebutuhanBahanService->porsiTersedia($menu, $jenisPersediaan);
    }

    protected function statusKetersediaan(Menu $menu, float $porsi): string
    {
        if (! $menu->resep_menu()->exists()) {
            return 'Resep Belum Lengkap';
        }
        if (! $menu->status_aktif) {
            return 'Nonaktif';
        }

        if ($porsi >= PHP_FLOAT_MAX) {
            return 'Tersedia';
        }

        if ($porsi <= 0) {
            return 'Stok Tidak Cukup';
        }

        return $porsi <= 3 ? 'Stok Menipis' : 'Tersedia';
    }
}
