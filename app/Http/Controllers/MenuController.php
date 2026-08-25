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
        
        if ($jenisId !== 'all') {
            if ($jenisId == 2) {
                // Paket Katering: HANYA tampilkan paket katering (memiliki komponen/item paket atau berada di kategori Catering)
                $query->where('jenis_menu_id', 2)->where(function($q) {
                    $q->has('item_paket')
                      ->orWhere('kategori_menu_id', 16);
                });
            } elseif ($jenisId == 3) {
                // Paket Nasi Box: HANYA tampilkan paket nasi box (memiliki komponen/item paket atau berada di kategori Nasi Box)
                $query->where('jenis_menu_id', 3)->where(function($q) {
                    $q->has('item_paket')
                      ->orWhere('kategori_menu_id', 17);
                });
            } else {
                $query->where('jenis_menu_id', $jenisId);
            }
        } else {
            // Semua Menu: Tampilkan semua menu utama (Dine In, Paket Katering, Paket Nasi Box) dan kecualikan item komponen opsi jika bukan show_komponen
            if ($request->input('show_komponen') != '1') {
                $query->where(function($q) {
                    $q->where('jenis_menu_id', 1)
                      ->orWhere(function($sub) {
                          $sub->whereIn('jenis_menu_id', [2, 3])
                              ->where(function($p) {
                                  $p->has('item_paket')
                                    ->orWhereIn('kategori_menu_id', [16, 17]);
                              });
                      });
                });
            }
        }

        $kategoriFilter = $request->input('kategori_id', $request->input('kategori'));
        if ($kategoriFilter && $kategoriFilter !== 'all') {
            $query->where('kategori_menu_id', $kategoriFilter);
        }

        if ($request->has('filter_resep') && $request->filter_resep != '') {
            if ($request->filter_resep == 'ada') {
                $query->where(function ($q) {
                    $q->whereHas('resep_menu')
                      ->orWhereHas('komponen_paket.menu_terkait.resep_menu')
                      ->orWhereHas('komponen_paket.opsi.menu.resep_menu');
                });
            } elseif ($request->filter_resep == 'belum') {
                $query->whereDoesntHave('resep_menu')
                      ->whereDoesntHave('komponen_paket.menu_terkait.resep_menu')
                      ->whereDoesntHave('komponen_paket.opsi.menu.resep_menu');
            }
        }

        if ($request->input('show_komponen') != '1' && $jenisId != 2 && $jenisId != 3) {
            $query->where('harga_jual', '>', 0);
        }

        $sort = $request->input('sort', 'nama');
        $sortMap = [
            'nama' => ['menu.nama_menu', 'asc'],
            'kategori' => ['kategori.nama_kategori', 'asc'],
            'harga' => ['menu.harga_jual', 'asc'],
            'terbaru' => ['menu.dibuat_pada', 'desc'],
        ];
        $sortCol = $sortMap[$sort][0] ?? $sortMap['nama'][0];
        $sortDir = $sortMap[$sort][1] ?? $sortMap['nama'][1];

        if ($sortCol === 'kategori.nama_kategori') {
            $query->leftJoin('kategori_menu as kategori', 'kategori.id', '=', 'menu.kategori_menu_id');
        }

        $menus = $query->orderBy($sortCol, $sortDir)
            ->orderBy('menu.nama_menu', 'asc')
            ->select('menu.*')
            ->paginate(10)
            ->withQueryString();
        $masterMenus = Menu::where('status_aktif', 1)->whereDoesntHave('komponen_paket')->orderBy('nama_menu')->get();

        $kategoris = KategoriMenu::withCount('menu')->orderBy('id', 'asc')->paginate(10)->withQueryString();
        $allKategoris = KategoriMenu::with('menu')->orderBy('id', 'asc')->get()->map(function($kat) {
            if ($kat->menu->isNotEmpty()) {
                $kat->setAttribute('jenis_menu_id', $kat->menu->first()->jenis_menu_id);
            }
            return $kat;
        });

        if ($jenisId == 2) {
            $allKategoris = $allKategoris->filter(fn($k) => $k->id == 16 || $k->jenis_menu_id == 2);
        } elseif ($jenisId == 3) {
            $allKategoris = $allKategoris->filter(fn($k) => $k->id == 17 || $k->jenis_menu_id == 3);
        } elseif ($jenisId == 1) {
            $allKategoris = $allKategoris->filter(fn($k) => !in_array($k->id, [16, 17]));
        }

        $bahanBakus = BahanBaku::with('satuan')->where('status_aktif', true)->orderBy('nama_bahan')->get();

        $stats = [
            'total' => Menu::where(function($q) {
                $q->where('jenis_menu_id', 1)
                  ->orWhere(function($sub) {
                      $sub->whereIn('jenis_menu_id', [2, 3])
                          ->where(function($p) {
                              $p->has('item_paket')
                                ->orWhereIn('kategori_menu_id', [16, 17]);
                          });
                  });
            })->count(),
            'dine_in' => Menu::where('jenis_menu_id', 1)->count(),
            'catering' => Menu::where('jenis_menu_id', 2)->where(fn($q) => $q->has('item_paket')->orWhere('kategori_menu_id', 16))->count(),
            'nasi_box' => Menu::where('jenis_menu_id', 3)->where(fn($q) => $q->has('item_paket')->orWhere('kategori_menu_id', 17))->count(),
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
        $allMenusData = Menu::with(['kategori_menu', 'resep_menu.bahan_baku.satuan', 'resep_menu.satuan'])->get();

        return view('admin.menu.index', compact('menus', 'kategoris', 'allKategoris', 'bahanBakus', 'satuans', 'stats', 'jenisId', 'allMenusData', 'masterMenus'));
    }

    public function create()
    {
        $kategoris = KategoriMenu::with('menu')->orderBy('id', 'asc')->get()->map(function($kat) {
            if ($kat->menu->isNotEmpty()) {
                $kat->setAttribute('jenis_menu_id', $kat->menu->first()->jenis_menu_id);
            }
            return $kat;
        });
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
            'minimal_pemesanan' => 'nullable|integer|min:1',
        ], [
            'nama_menu.required' => 'Nama menu wajib diisi.',
            'kategori_menu_id.required' => 'Kategori menu wajib dipilih.',
            'harga_jual.required' => 'Harga jual menu wajib diisi.',
            'harga_jual.numeric' => 'Harga jual harus berupa angka.',
            'harga_jual.min' => 'Harga jual minimal Rp 0.',
            'foto.image' => 'Foto harus berupa berkas gambar.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
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
                'minimal_pemesanan' => $request->minimal_pemesanan ?? ($jenisId == 2 ? 50 : 20),
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
                foreach ($request->komponen as $index => $komp) {
                    $tipe = $komp['tipe'] ?? 'wajib';
                    
                    // Validation
                    if ($tipe === 'wajib' && empty($komp['menu_id'])) continue;
                    if (in_array($tipe, ['pilihan', 'semua_didapat']) && empty($komp['nama_komponen'])) continue;

                    $menuTerkait = null;
                    $namaItem = $komp['nama_komponen'] ?? '';

                    if ($tipe === 'wajib') {
                        $menuTerkait = Menu::find($komp['menu_id']);
                        if ($menuTerkait && empty($namaItem)) {
                            $namaItem = $menuTerkait->nama_menu;
                        }
                    }

                    $minPilihan = ($tipe === 'pilihan') ? (int)($komp['min_pilihan'] ?? 1) : 0;
                    $maxPilihan = ($tipe === 'pilihan') ? (int)($komp['max_pilihan'] ?? 1) : 0;

                    $komponen = \App\Models\ItemPaket::create([
                        'menu_id' => $menu->id,
                        'nama_item' => $namaItem,
                        'tipe_item' => $tipe,
                        'menu_id_terkait' => $tipe === 'wajib' ? $komp['menu_id'] : null,
                        'jumlah' => $komp['jumlah'] ?? 1,
                        'minimum_pilihan' => $minPilihan,
                        'maksimum_pilihan' => $maxPilihan,
                        'urutan' => $komp['urutan'] ?? 1,
                    ]);

                    if (in_array($tipe, ['pilihan', 'semua_didapat']) && !empty($komp['pilihan']) && is_array($komp['pilihan'])) {
                        $urutanPilihan = 1;
                        $urutanPilihan_0 = 0;
                        foreach ($komp['pilihan'] as $pilihanText) {
                            $pilihanText = trim($pilihanText);
                            if ($pilihanText) {
                                $pilihanData = [
                                    'item_paket_id' => $komponen->id,
                                    'menu_id' => null,
                                    'nama_pilihan' => $pilihanText,
                                    'urutan' => $urutanPilihan,
                                ];
                                if ($request->hasFile("komponen.$index.opsi_foto.$urutanPilihan_0")) {
                                    $pilihanData['foto'] = $request->file("komponen.$index.opsi_foto.$urutanPilihan_0")->store('paket-opsi', 'public');
                                } elseif (!empty($komp['opsi_foto_existing'][$urutanPilihan_0])) {
                                    $pilihanData['foto'] = $komp['opsi_foto_existing'][$urutanPilihan_0];
                                }
                                \App\Models\PilihanItemPaket::create($pilihanData);
                                $urutanPilihan++;
                            }
                            $urutanPilihan_0++;
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
            'minimal_pemesanan' => 'nullable|integer|min:1',
        ], [
            'nama_menu.required' => 'Nama menu wajib diisi.',
            'kategori_menu_id.required' => 'Kategori menu wajib dipilih.',
            'harga_jual.required' => 'Harga jual menu wajib diisi.',
            'harga_jual.numeric' => 'Harga jual harus berupa angka.',
            'harga_jual.min' => 'Harga jual minimal Rp 0.',
            'foto.image' => 'Foto harus berupa berkas gambar.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
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
                'minimal_pemesanan' => $request->minimal_pemesanan ?? ($jenisId == 2 ? 50 : 20),
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
                foreach ($request->komponen as $index => $komp) {
                    $tipe = $komp['tipe'] ?? 'wajib';
                    
                    // Validation
                    if ($tipe === 'wajib' && empty($komp['menu_id'])) continue;
                    if (in_array($tipe, ['pilihan', 'semua_didapat']) && empty($komp['nama_komponen'])) continue;

                    $menuTerkait = null;
                    $namaItem = $komp['nama_komponen'] ?? '';

                    if ($tipe === 'wajib') {
                        $menuTerkait = Menu::find($komp['menu_id']);
                        if ($menuTerkait && empty($namaItem)) {
                            $namaItem = $menuTerkait->nama_menu;
                        }
                    }

                    $minPilihan = ($tipe === 'pilihan') ? (int)($komp['min_pilihan'] ?? 1) : 0;
                    $maxPilihan = ($tipe === 'pilihan') ? (int)($komp['max_pilihan'] ?? 1) : 0;

                    $komponen = \App\Models\ItemPaket::create([
                        'menu_id' => $menu->id,
                        'nama_item' => $namaItem,
                        'tipe_item' => $tipe,
                        'menu_id_terkait' => $tipe === 'wajib' ? $komp['menu_id'] : null,
                        'jumlah' => $komp['jumlah'] ?? 1,
                        'minimum_pilihan' => $minPilihan,
                        'maksimum_pilihan' => $maxPilihan,
                        'urutan' => $komp['urutan'] ?? 1,
                    ]);

                    if (in_array($tipe, ['pilihan', 'semua_didapat']) && !empty($komp['pilihan']) && is_array($komp['pilihan'])) {
                        $urutanPilihan = 1;
                        $urutanPilihan_0 = 0;
                        foreach ($komp['pilihan'] as $pilihanText) {
                            $pilihanText = trim($pilihanText);
                            if ($pilihanText) {
                                $pilihanData = [
                                    'item_paket_id' => $komponen->id,
                                    'menu_id' => null,
                                    'nama_pilihan' => $pilihanText,
                                    'urutan' => $urutanPilihan,
                                ];
                                if ($request->hasFile("komponen.$index.opsi_foto.$urutanPilihan_0")) {
                                    $pilihanData['foto'] = $request->file("komponen.$index.opsi_foto.$urutanPilihan_0")->store('paket-opsi', 'public');
                                } elseif (!empty($komp['opsi_foto_existing'][$urutanPilihan_0])) {
                                    $pilihanData['foto'] = $komp['opsi_foto_existing'][$urutanPilihan_0];
                                }
                                \App\Models\PilihanItemPaket::create($pilihanData);
                                $urutanPilihan++;
                            }
                            $urutanPilihan_0++;
                        }
                    }
                }
            }

            return redirect()->route('menu.index')->with('success', "Menu '{$menu->nama_menu}' berhasil diperbarui.");
        });
    }

    public function show(Menu $menu)
    {
        $menu->load(['kategori_menu', 'resep_menu.bahan_baku.satuan', 'resep_menu.satuan', 'jenis_menu', 'komponen_paket.pilihanItemPaket.menu']);

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
                $menu->update(['status_aktif' => false]);
                return redirect()->route('menu.index')->with('success', "Menu '{$menu->nama_menu}' tidak dihapus permanen melainkan Dinonaktifkan karena sudah memiliki data transaksi.");
            }

            return redirect()->route('menu.index')->with('error', 'Gagal menghapus menu: Terjadi kesalahan database. '.$e->getMessage());
        }
    }

    public function createFromOption(Request $request)
    {
        $request->validate([
            'opsi_id' => 'required|exists:pilihan_item_paket,id',
            'nama_menu' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $opsi = \App\Models\PilihanItemPaket::findOrFail($request->opsi_id);
            $itemPaket = \App\Models\ItemPaket::findOrFail($opsi->item_paket_id);
            $paket = Menu::findOrFail($itemPaket->menu_id);

            // Create new menu
            $menu = Menu::create([
                'id_menu' => 'MN-' . time() . '-' . rand(100, 999),
                'nama_menu' => $request->nama_menu,
                'jenis_menu_id' => 1, // Default Dine In
                'kategori_menu_id' => $paket->kategori_menu_id,
                'harga_jual' => 0,
                'status_aktif' => 1,
            ]);

            // Link it to the option
            $opsi->menu_id = $menu->id;
            $opsi->save();

            DB::commit();

            return response()->json(['success' => true, 'menu_id' => $menu->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal membuat menu: ' . $e->getMessage()], 500);
        }
    }
}
