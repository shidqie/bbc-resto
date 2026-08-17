<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\JenisMenu;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\StokBahan;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index(Request $request, \App\Services\KebutuhanBahanService $kebutuhanBahanService)
    {
        $query = Menu::with([
            'kategori_menu', 
            'resep_menu.bahan_baku.satuan', 
            'resep_menu.satuan', 
            'jenis_menu', 
            'komponen_paket.opsi.menu.resep_menu',
            'komponen_paket.opsi.menu.kategori_menu',
            'komponen_paket.menu_terkait.resep_menu',
            'komponen_paket.menu_terkait.kategori_menu'
        ]);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_menu', 'like', "%{$search}%")
                    ->orWhere('id_menu', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan jenis menu
        $jenisId = $request->input('jenis_menu_id', 1); // default: Menu Dine In
        
        if ($jenisId == 'catering') $jenisId = 2;
        if ($jenisId == 'nasi_box') $jenisId = 3;
        
        $query->where('jenis_menu_id', $jenisId);

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_menu_id', $request->kategori);
        }

        if ($request->has('filter_resep') && $request->filter_resep != '') {
            if ($request->filter_resep == 'ada') {
                $query->has('resep_menu');
            } elseif ($request->filter_resep == 'belum') {
                $query->doesntHave('resep_menu');
            }
        }

        $menus = $query->orderBy('id', 'asc')->paginate(10)->withQueryString();

        $kategoris = KategoriMenu::withCount('menu')->orderBy('id', 'asc')->paginate(10)->withQueryString();
        $allKategoris = KategoriMenu::orderBy('id', 'asc')->get();

        $bahanBakus = BahanBaku::with('satuan')->where('status_aktif', true)->orderBy('nama_bahan')->get();

        $stats = [
            'total' => Menu::count(),
            'dine_in' => Menu::where('jenis_menu_id', 1)->count(),
            'catering' => Menu::where('jenis_menu_id', 2)->count(),
            'nasi_box' => Menu::where('jenis_menu_id', 3)->count(),
        ];

        $stokBahan = StokBahan::harian()->pluck('jumlah_stok', 'bahan_baku_id');
        $menus->getCollection()->transform(function ($menu) use ($stokBahan, $kebutuhanBahanService) {
            $porsi = null;
            if ($menu->resep_menu->isNotEmpty()) {
                foreach ($menu->resep_menu as $resep) {
                    $stok = (float) ($stokBahan[$resep->bahan_baku_id] ?? 0);
                    $butuh = (float) $resep->jumlah;
                    $bisa = $butuh > 0 ? (int) floor($stok / $butuh) : PHP_INT_MAX;
                    $porsi = $porsi === null ? $bisa : min($porsi, $bisa);
                }
            }
            $menu->setAttribute('porsi_tersedia', $porsi);

            // Compute aggregations for Paket
            if (in_array($menu->jenis_menu_id, [2, 3])) {
                $kebutuhan = $kebutuhanBahanService->kebutuhanMenu($menu, 1);
                $menu->setAttribute('kebutuhan_paket', $kebutuhan);
                
                // Set recipe status for components
                $komponenStatus = [];
                foreach ($menu->komponen_paket as $komp) {
                    if ($komp->tipe_item === 'tetap' && $komp->menu_terkait) {
                        $komponenStatus[$komp->id] = $komp->menu_terkait->resep_menu->isNotEmpty() ? 'Lengkap' : 'Belum Lengkap';
                    } elseif ($komp->tipe_item === 'pilihan') {
                        $status = 'Lengkap';
                        foreach ($komp->opsi as $opsi) {
                            if ($opsi->menu && $opsi->menu->resep_menu->isEmpty()) {
                                $status = 'Belum Lengkap';
                                break;
                            }
                        }
                        $komponenStatus[$komp->id] = $status;
                    } else {
                        $komponenStatus[$komp->id] = 'Belum Lengkap';
                    }
                }
                $menu->setAttribute('status_resep_komponen', $komponenStatus);
            }

            return $menu;
        });

        $satuans = \App\Models\Satuan::all();
        $allMenusData = Menu::with('kategori_menu', 'resep_menu.bahan_baku.satuan')->get();

        return view('admin.menu.index', compact('menus', 'kategoris', 'allKategoris', 'bahanBakus', 'satuans', 'stats', 'jenisId', 'allMenusData'));
    }

    public function create()
    {
        $kategoris = KategoriMenu::orderBy('id')->get();
        $bahanBakus = BahanBaku::with('satuan')->where('status_aktif', true)->orderBy('nama_bahan')->get();
        $jenis_menu = JenisMenu::all();

        return view('admin.menu.create', compact('kategoris', 'bahanBakus', 'jenis_menu'));
    }

    public function store(Request $request)
    {
        $namaMenu = $request->input('nama_menu') ?? $request->input('nama');
        $hargaJual = $request->input('harga_jual') ?? $request->input('harga');

        $request->merge([
            'nama_menu' => $namaMenu,
            'harga_jual' => $hargaJual,
        ]);

        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'kategori_menu_id' => 'required|exists:kategori_menu,id',
            'harga_jual' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'bahan_baku_id' => 'nullable|array',
            'jumlah_kebutuhan' => 'nullable|array',
        ]);

        $jenisId = match ((string) $request->input('jenis_menu_id')) {
            'catering', '2' => 2,
            'nasi_box', '3' => 3,
            default => 1
        };

        $statusAktif = match (true) {
            $request->has('status') => in_array($request->status, ['tersedia', 'aktif', '1', 'on'], true),
            $request->has('status_aktif') => in_array($request->status_aktif, ['tersedia', 'aktif', '1', 'on'], true),
            default => true,
        };

        return DB::transaction(function () use ($request, $namaMenu, $hargaJual, $jenisId, $statusAktif) {
            $data = [
                'nama_menu' => $namaMenu,
                'id_menu' => 'PRD-'.strtoupper(uniqid()),
                'kategori_menu_id' => $request->kategori_menu_id,
                'jenis_menu_id' => $jenisId,
                'harga_jual' => $hargaJual,
                'deskripsi' => $request->deskripsi,
                'status_aktif' => $statusAktif,
            ];

            if ($request->hasFile('foto')) {
                $path = $request->file('foto')->store('menu', 'public');
                $data['foto'] = $path;
            }

            $menu = Menu::create($data);

            if ($request->has('bahan_baku_id') && is_array($request->bahan_baku_id)) {
                foreach ($request->bahan_baku_id as $index => $bahanId) {
                    if (empty($bahanId) || empty($request->jumlah_kebutuhan[$index])) {
                        continue;
                    }
                    $bahan = BahanBaku::find($bahanId);
                    if ($bahan) {
                        \App\Models\ResepMenu::create([
                            'menu_id' => $menu->id,
                            'bahan_baku_id' => $bahanId,
                            'jumlah_kebutuhan' => $request->jumlah_kebutuhan[$index],
                            'satuan_id' => $request->satuan_id[$index] ?? $bahan->satuan_id ?? 1,
                            'keterangan' => $request->keterangan[$index] ?? null,
                            'dikonfirmasi' => true,
                        ]);
                    }
                }
            }

            if ($request->has('komponen') && is_array($request->komponen)) {
                foreach ($request->komponen as $komp) {
                    $tipe = (isset($komp['tipe']) && $komp['tipe'] === 'choice') ? 'pilihan' : 'tetap';
                    
                    // Validation: if "tetap", menu_id is required. if "choice", nama_komponen is required.
                    if ($tipe === 'tetap' && empty($komp['menu_id'])) continue;
                    if ($tipe === 'pilihan' && empty($komp['nama_komponen'])) continue;

                    $menuTerkait = null;
                    $namaItem = $komp['nama_komponen'] ?? '';

                    if ($tipe === 'tetap') {
                        $menuTerkait = Menu::find($komp['menu_id']);
                        if ($menuTerkait && empty($namaItem)) {
                            $namaItem = $menuTerkait->nama_menu;
                        }
                    }

                    $komponen = \App\Models\ItemPaket::create([
                        'menu_id' => $menu->id,
                        'nama_item' => $namaItem,
                        'tipe_item' => $tipe,
                        'menu_id_terkait' => $tipe === 'tetap' ? $komp['menu_id'] : null,
                        'jumlah' => $komp['jumlah'] ?? 1,
                        'minimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                        'maksimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                        'urutan' => $komp['urutan'] ?? 1,
                    ]);

                    if ($tipe === 'pilihan' && !empty($komp['pilihan']) && is_array($komp['pilihan'])) {
                        $urutanPilihan = 1;
                        foreach ($komp['pilihan'] as $pilihanMenuId) {
                            $menuOpsi = Menu::find($pilihanMenuId);
                            if ($menuOpsi) {
                                \App\Models\PilihanItemPaket::create([
                                    'item_paket_id' => $komponen->id,
                                    'menu_id' => $pilihanMenuId,
                                    'nama_pilihan' => $menuOpsi->nama_menu,
                                    'urutan' => $urutanPilihan++,
                                ]);
                            }
                        }
                    }
                }
            }

            return redirect()->route('menu.index')->with('success', "Menu '{$menu->nama_menu}' berhasil disimpan.");
        });
    }

    public function edit(Menu $menu)
    {
        $menu->load('resep_menu.bahan_baku.satuan');
        $kategoris = KategoriMenu::orderBy('id')->get();
        $bahanBakus = BahanBaku::with('satuan')->where('status_aktif', true)->orderBy('nama_bahan')->get();
        $jenis_menu = JenisMenu::all();

        return view('admin.menu.edit', compact('menu', 'kategoris', 'bahanBakus', 'jenis_menu'));
    }

    public function update(Request $request, Menu $menu)
    {
        $namaMenu = $request->input('nama_menu') ?? $request->input('nama');
        $hargaJual = $request->input('harga_jual') ?? $request->input('harga');

        $request->merge([
            'nama_menu' => $namaMenu,
            'harga_jual' => $hargaJual,
        ]);

        $request->validate([
            'nama_menu' => 'required|string|max:255',
            'kategori_menu_id' => 'required|exists:kategori_menu,id',
            'harga_jual' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'bahan_baku_id' => 'nullable|array',
            'jumlah_kebutuhan' => 'nullable|array',
        ]);

        $jenisId = match ((string) $request->input('jenis_menu_id')) {
            'catering', '2' => 2,
            'nasi_box', '3' => 3,
            default => 1
        };

        $statusAktif = match (true) {
            $request->has('status') => in_array($request->status, ['tersedia', 'aktif', '1', 'on'], true),
            $request->has('status_aktif') => in_array($request->status_aktif, ['tersedia', 'aktif', '1', 'on'], true),
            default => true,
        };

        return DB::transaction(function () use ($request, $menu, $namaMenu, $hargaJual, $jenisId, $statusAktif) {
            $data = [
                'nama_menu' => $namaMenu,
                'kategori_menu_id' => $request->kategori_menu_id,
                'jenis_menu_id' => $jenisId,
                'harga_jual' => $hargaJual,
                'deskripsi' => $request->deskripsi,
                'status_aktif' => $statusAktif,
            ];

            if ($request->hasFile('foto')) {
                if ($menu->foto && Storage::disk('public')->exists($menu->foto)) {
                    Storage::disk('public')->delete($menu->foto);
                }
                $path = $request->file('foto')->store('menu', 'public');
                $data['foto'] = $path;
            }

            $menu->update($data);

            $menu->resep_menu()->delete(); // Always clear existing recipes
            if ($request->has('bahan_baku_id') && is_array($request->bahan_baku_id)) {
                foreach ($request->bahan_baku_id as $index => $bahanId) {
                    if (empty($bahanId) || empty($request->jumlah_kebutuhan[$index])) {
                        continue;
                    }
                    $bahan = BahanBaku::find($bahanId);
                    if ($bahan) {
                        \App\Models\ResepMenu::create([
                            'menu_id' => $menu->id,
                            'bahan_baku_id' => $bahanId,
                            'jumlah_kebutuhan' => $request->jumlah_kebutuhan[$index],
                            'satuan_id' => $request->satuan_id[$index] ?? $bahan->satuan_id ?? 1,
                            'keterangan' => $request->keterangan[$index] ?? null,
                            'dikonfirmasi' => true,
                        ]);
                    }
                }
            }

            $menu->komponen_paket()->delete(); // Always clear existing components
            if ($request->has('komponen') && is_array($request->komponen)) {
                foreach ($request->komponen as $komp) {
                    $tipe = (isset($komp['tipe']) && $komp['tipe'] === 'choice') ? 'pilihan' : 'tetap';
                    
                    if ($tipe === 'tetap' && empty($komp['menu_id'])) continue;
                    if ($tipe === 'pilihan' && empty($komp['nama_komponen'])) continue;

                    $menuTerkait = null;
                    $namaItem = $komp['nama_komponen'] ?? '';

                    if ($tipe === 'tetap') {
                        $menuTerkait = Menu::find($komp['menu_id']);
                        if ($menuTerkait && empty($namaItem)) {
                            $namaItem = $menuTerkait->nama_menu;
                        }
                    }

                    $komponen = \App\Models\ItemPaket::create([
                        'menu_id' => $menu->id,
                        'nama_item' => $namaItem,
                        'tipe_item' => $tipe,
                        'menu_id_terkait' => $tipe === 'tetap' ? $komp['menu_id'] : null,
                        'jumlah' => $komp['jumlah'] ?? 1,
                        'minimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                        'maksimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                        'urutan' => $komp['urutan'] ?? 1,
                    ]);

                    if ($tipe === 'pilihan' && !empty($komp['pilihan']) && is_array($komp['pilihan'])) {
                        $urutanPilihan = 1;
                        foreach ($komp['pilihan'] as $pilihanMenuId) {
                            $menuOpsi = Menu::find($pilihanMenuId);
                            if ($menuOpsi) {
                                \App\Models\PilihanItemPaket::create([
                                    'item_paket_id' => $komponen->id,
                                    'menu_id' => $pilihanMenuId,
                                    'nama_pilihan' => $menuOpsi->nama_menu,
                                    'urutan' => $urutanPilihan++,
                                ]);
                            }
                        }
                    }
                }
            }

            return redirect()->route('menu.index')->with('success', "Menu '{$menu->nama_menu}' berhasil diperbarui.");
        });
    }

    public function show(Menu $menu)
    {
        $menu->load(['kategori_menu', 'resep_menu.bahan_baku.satuan', 'resep_menu.satuan', 'jenis_menu']);

        return view('admin.menu.show', compact('menu'));
    }

    public function toggleStatus(Menu $menu)
    {
        $newStatus = ! $menu->status_aktif;
        $menu->update(['status_aktif' => $newStatus]);

        return redirect()->route('menu.index')->with('success', "Status menu '{$menu->nama_menu}' berhasil diubah.");
    }

    public function destroy(Menu $menu)
    {
        try {
            return DB::transaction(function () use ($menu) {
                if ($menu->foto && Storage::disk('public')->exists($menu->foto)) {
                    Storage::disk('public')->delete($menu->foto);
                }
                $menu->resep_menu()->delete();
                $menu->komponen_paket()->delete(); // Hapus relasi komponen jika ada
                $menu->delete();

                return redirect()->route('menu.index')->with('success', "Menu '{$menu->nama_menu}' berhasil dihapus.");
            });
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1] ?? $e->getCode();
            if ($errorCode == 1451 || $errorCode == '23000' || strpos($e->getMessage(), '1451') !== false) {
                return redirect()->route('menu.index')->with('error', "Menu '{$menu->nama_menu}' tidak dapat dihapus karena sudah ada di data pesanan. Silakan ubah status menjadi Nonaktif.");
            }

            return redirect()->route('menu.index')->with('error', 'Gagal menghapus menu: Terjadi kesalahan database. '.$e->getMessage());
        }
    }
}
