<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\JenisMenu;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\ResepMenu;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::with(['kategori_menu', 'resep_menu.bahan_baku.satuan', 'jenis_menu']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_menu', 'like', "%{$search}%")
                    ->orWhere('kode_menu', 'like', "%{$search}%");
            });
        }

        // Hanya tampilkan menu Dine In (jenis_menu_id = 1)
        $query->where('jenis_menu_id', 1);

        if ($request->has('kategori') && $request->kategori != '') {
            $query->where('kategori_menu_id', $request->kategori);
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

        return view('menu.menu.index', compact('menus', 'kategoris', 'allKategoris', 'bahanBakus', 'stats'));
    }

    public function create()
    {
        $kategoris = KategoriMenu::orderBy('id')->get();
        $bahanBakus = BahanBaku::with('satuan')->where('status_aktif', true)->orderBy('nama_bahan')->get();
        $jenis_menu = JenisMenu::all();

        return view('menu.menu.create', compact('kategoris', 'bahanBakus', 'jenis_menu'));
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
                'kode_menu' => 'PRD-'.strtoupper(uniqid()),
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
                $jumlahCol = DB::getSchemaBuilder()->hasColumn('resep_menu', 'jumlah_kebutuhan') ? 'jumlah_kebutuhan' : 'jumlah';
                $hasSatuanCol = DB::getSchemaBuilder()->hasColumn('resep_menu', 'satuan_id');

                foreach ($request->bahan_baku_id as $idx => $bahanId) {
                    $qty = $request->jumlah_kebutuhan[$idx] ?? 0;
                    if ($bahanId && $qty > 0) {
                        $bahan = BahanBaku::find($bahanId);
                        $resepData = [
                            'menu_id' => $menu->id,
                            'bahan_baku_id' => $bahanId,
                            $jumlahCol => $qty,
                        ];
                        if ($hasSatuanCol) {
                            $resepData['satuan_id'] = $bahan->satuan_id ?? 1;
                        }
                        ResepMenu::create($resepData);
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

        return view('menu.menu.edit', compact('menu', 'kategoris', 'bahanBakus', 'jenis_menu'));
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

            $menu->resep_menu()->delete();

            if ($request->has('bahan_baku_id') && is_array($request->bahan_baku_id)) {
                $jumlahCol = DB::getSchemaBuilder()->hasColumn('resep_menu', 'jumlah_kebutuhan') ? 'jumlah_kebutuhan' : 'jumlah';
                $hasSatuanCol = DB::getSchemaBuilder()->hasColumn('resep_menu', 'satuan_id');

                foreach ($request->bahan_baku_id as $idx => $bahanId) {
                    $qty = $request->jumlah_kebutuhan[$idx] ?? 0;
                    if ($bahanId && $qty > 0) {
                        $bahan = BahanBaku::find($bahanId);
                        $resepData = [
                            'menu_id' => $menu->id,
                            'bahan_baku_id' => $bahanId,
                            $jumlahCol => $qty,
                        ];
                        if ($hasSatuanCol) {
                            $resepData['satuan_id'] = $bahan->satuan_id ?? 1;
                        }
                        ResepMenu::create($resepData);
                    }
                }
            }

            return redirect()->route('menu.index')->with('success', "Menu '{$menu->nama_menu}' berhasil diperbarui.");
        });
    }

    public function show(Menu $menu)
    {
        $menu->load(['kategori_menu', 'resep_menu.bahan_baku.satuan', 'jenis_menu']);

        return view('menu.menu.show', compact('menu'));
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
