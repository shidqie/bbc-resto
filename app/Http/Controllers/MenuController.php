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
    public function index(Request $request)
    {
        $query = Menu::with(['kategori_menu', 'resep_menu.bahan_baku.satuan', 'resep_menu.satuan', 'jenis_menu', 'komponen_paket.opsi']);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_menu', 'like', "%{$search}%")
                    ->orWhere('kode_menu', 'like', "%{$search}%");
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
        $menus->getCollection()->transform(function ($menu) use ($stokBahan) {
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

            return $menu;
        });

        $satuans = \App\Models\Satuan::all();

        return view('menu.menu.index', compact('menus', 'kategoris', 'allKategoris', 'bahanBakus', 'satuans', 'stats', 'jenisId'));
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
                    if (empty($komp['nama_komponen'])) continue;
                    $tipe = (isset($komp['tipe']) && $komp['tipe'] === 'choice') ? 'pilihan' : 'tetap';
                    $komponen = \App\Models\ItemPaket::create([
                        'menu_id' => $menu->id,
                        'nama_item' => $komp['nama_komponen'],
                        'tipe_item' => $tipe,
                        'minimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                        'maksimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                        'urutan' => $komp['urutan'] ?? 1,
                    ]);

                    if ($tipe === 'pilihan' && ! empty($komp['pilihan'])) {
                        $pilihanList = array_map('trim', explode(',', $komp['pilihan']));
                        $urutanPilihan = 1;
                        foreach ($pilihanList as $namaPilihan) {
                            if (! empty($namaPilihan)) {
                                \App\Models\PilihanItemPaket::create([
                                    'item_paket_id' => $komponen->id,
                                    'nama_pilihan' => $namaPilihan,
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

            if ($request->has('bahan_baku_id') && is_array($request->bahan_baku_id)) {
                $menu->resep_menu()->delete(); // Clear existing
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
                $menu->komponen_paket()->delete(); // Clear existing
                foreach ($request->komponen as $komp) {
                    if (empty($komp['nama_komponen'])) continue;
                    $tipe = (isset($komp['tipe']) && $komp['tipe'] === 'choice') ? 'pilihan' : 'tetap';
                    $komponen = \App\Models\ItemPaket::create([
                        'menu_id' => $menu->id,
                        'nama_item' => $komp['nama_komponen'],
                        'tipe_item' => $tipe,
                        'minimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                        'maksimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                        'urutan' => $komp['urutan'] ?? 1,
                    ]);

                    if ($tipe === 'pilihan' && ! empty($komp['pilihan'])) {
                        $pilihanList = array_map('trim', explode(',', $komp['pilihan']));
                        $urutanPilihan = 1;
                        foreach ($pilihanList as $namaPilihan) {
                            if (! empty($namaPilihan)) {
                                \App\Models\PilihanItemPaket::create([
                                    'item_paket_id' => $komponen->id,
                                    'nama_pilihan' => $namaPilihan,
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
