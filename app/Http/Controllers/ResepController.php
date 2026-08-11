<?php

namespace App\Http\Controllers;

use App\Models\BahanBaku;
use App\Models\ItemPaket;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Models\PilihanItemPaket;
use App\Models\ResepMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResepController extends Controller
{
    protected $daftarSatuanSajian = ['porsi', 'pcs', 'bungkus', 'cup', 'botol', 'gelas', 'potong', 'buah', 'slice'];

    public function index(Request $request)
    {
        $search = $request->search;
        $kategoriId = $request->kategori;
        $layananId = $request->layanan;
        $statusResep = $request->status_resep;

        $menus = Menu::with([
            'kategori_menu',
            'jenis_menu',
            'resep_menu.bahan_baku.satuan',
            'komponen_paket.opsi.menu.resep_menu',
            'komponen_paket.opsi.menu.kategori_menu',
            'komponen_paket.menu_terkait.resep_menu',
            'komponen_paket.menu_terkait.kategori_menu',
        ])
            ->withCount('resep_menu', 'komponen_paket')
            ->when($search, function ($q) use ($search) {
                $q->where('nama_menu', 'like', "%{$search}%")
                    ->orWhere('id_menu', 'like', "%{$search}%");
            })
            ->when($kategoriId, function ($q) use ($kategoriId) {
                $q->where('kategori_menu_id', $kategoriId);
            })
            ->when($layananId, function ($q) use ($layananId) {
                $q->where('jenis_menu_id', $layananId);
            })
            ->when($statusResep === 'ada', function ($q) {
                $q->whereHas('resep_menu');
            })
            ->when($statusResep === 'belum', function ($q) {
                $q->whereDoesntHave('resep_menu');
            })
            ->orderBy('nama_menu', 'asc')
            ->paginate(10)->withQueryString();

        $kategoris = KategoriMenu::orderBy('nama_kategori', 'asc')->get();
        $bahanBakus = BahanBaku::with('satuan')->orderBy('nama_bahan', 'asc')->get();

        // Menu yang sudah dipakai (transaksi / dipakai paket) → jangan tampilkan tombol hapus menu
        $menuUsedIds = DB::table('detail_pesanan')->distinct()->pluck('menu_id')
            ->merge(DB::table('item_paket')->whereNotNull('menu_id_terkait')->distinct()->pluck('menu_id_terkait'))
            ->merge(DB::table('pilihan_item_paket')->whereNotNull('menu_id')->distinct()->pluck('menu_id'))
            ->unique()->values();

        // Semua menu satuan (bukan paket) untuk lookup + dropdown komposisi
        $semuaMenu = Menu::with('resep_menu.bahan_baku.satuan')
            ->withCount('resep_menu')
            ->get();

        $menuById = $semuaMenu->keyBy('id');
        $menuByName = $semuaMenu->keyBy(fn ($m) => strtolower(trim($m->nama_menu)));

        // Data untuk drawer Atur Resep
        $jsResepMenus = $semuaMenu->map(fn ($m) => [
            'id' => $m->id,
            'nama_menu' => $m->nama_menu,
            'id_menu' => $m->id_menu,
            'jenis_menu_id' => $m->jenis_menu_id,
            'kategori_menu_id' => $m->kategori_menu_id,
            'harga_jual' => $m->harga_jual,
            'deskripsi' => $m->deskripsi,
            'status_aktif' => $m->status_aktif,
            'foto' => $m->foto,
            'resep_menu_count' => $m->resep_menu_count,
            'resep_lengkap' => $m->resep_menu->count() > 0 && $m->resep_menu->every(fn ($r) => $r->dikonfirmasi),
            'resep_menu' => $m->resep_menu->map(fn ($r) => [
                'bahan_baku_id' => $r->bahan_baku_id,
                'jumlah_kebutuhan' => $r->jumlah_kebutuhan ?? $r->jumlah ?? '',
                'satuan_id' => $r->satuan_id,
                'keterangan' => $r->keterangan,
                'dikonfirmasi' => (bool) $r->dikonfirmasi,
            ])->toArray(),
        ])->values();

        // Data untuk drawer Atur Komposisi (per paket)
        $jsPaketKomposisi = [];
        $menuSatuanOptions = $semuaMenu->map(fn ($m) => [
            'id' => $m->id,
            'nama_menu' => $m->nama_menu,
            'id_menu' => $m->id_menu,
            'resep_menu_count' => $m->resep_menu_count,
            'resep_lengkap' => $m->resep_menu->count() > 0 && $m->resep_menu->every(fn ($r) => $r->dikonfirmasi),
        ])->values();

        $paketList = Menu::whereHas('komponen_paket')->with('komponen_paket.opsi.menu', 'komponen_paket.menu_terkait')->get();        foreach ($paketList as $paket) {
            $jsPaketKomposisi[$paket->id] = [
                'id' => $paket->id,
                'nama_menu' => $paket->nama_menu,
                'id_menu' => $paket->id_menu,
                'komponen' => $paket->komponen_paket->map(fn ($k) => [
                    'id' => $k->id,
                    'nama_item' => $k->nama_item,
                    'tipe_item' => $k->tipe_item,
                    'menu_id_terkait' => $k->menu_id_terkait,
                    'jumlah' => (float) ($k->jumlah ?? 1),
                    'satuan_sajian' => $k->satuan_sajian ?? 'porsi',
                    'minimum_pilihan' => $k->minimum_pilihan,
                    'maksimum_pilihan' => $k->maksimum_pilihan,
                    'urutan' => $k->urutan,
                    'opsi' => $k->opsi->map(fn ($o) => [
                        'id' => $o->id,
                        'nama_pilihan' => $o->nama_pilihan,
                        'menu_id' => $o->menu_id,
                        'jumlah' => (float) ($o->jumlah ?? 1),
                        'satuan_sajian' => $o->satuan_sajian ?? 'porsi',
                    ])->toArray(),
                ])->toArray(),
            ];
        }

        return view('admin.menu.resep.index', compact(
            'menus', 'search', 'kategoriId', 'layananId', 'statusResep',
            'kategoris', 'bahanBakus', 'menuById', 'menuByName', 'menuUsedIds',
            'jsResepMenus', 'jsPaketKomposisi', 'menuSatuanOptions'
        ))->with('daftarSatuanSajian', $this->daftarSatuanSajian);
    }

    public function create(Menu $menu)
    {
        $menu->load('resep_menu.bahan_baku.satuan');

        $bahanBakus = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();

        $totalHpp = 0;
        foreach ($menu->resep_menu as $resep) {
            $hargaSatuan = $resep->bahan_baku->harga_satuan ?? 0;
            $totalHpp += $hargaSatuan * $resep->jumlah_kebutuhan;
        }

        return view('admin.menu.resep.create', compact('menu', 'bahanBakus', 'totalHpp'));
    }

    public function store(Request $request, Menu $menu)
    {
        $request->validate([
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'required|exists:bahan_baku,id|distinct',
            'jumlah_kebutuhan' => 'required|array',
            'jumlah_kebutuhan.*' => 'required|numeric|gt:0',
        ]);

        try {
            DB::beginTransaction();

            $menu->resep_menu()->delete();

            $dikonfirmasi = $request->boolean('dikonfirmasi');

            foreach ($request->bahan_baku_id as $index => $bahanId) {
                $bahan = BahanBaku::find($bahanId);
                ResepMenu::create([
                    'menu_id' => $menu->id,
                    'bahan_baku_id' => $bahanId,
                    'jumlah_kebutuhan' => $request->jumlah_kebutuhan[$index],
                    'satuan_id' => $bahan->satuan_id ?? 1,
                    'keterangan' => $request->keterangan[$index] ?? null,
                    'dikonfirmasi' => $dikonfirmasi,
                ]);
            }

            DB::commit();

            return redirect()->route('resep.index')->with('success', "Resep untuk menu {$menu->nama_menu} berhasil diperbarui.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat menyimpan resep: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Menu $menu)
    {
        try {
            DB::beginTransaction();

            $menu->resep_menu()->delete();

            DB::commit();

            return redirect()->route('resep.index')->with('success', "Resep untuk menu {$menu->nama_menu} berhasil dihapus.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat menghapus resep: '.$e->getMessage());
        }
    }

    /**
     * Simpan komposisi paket: menu tetap (item_paket) + kelompok pilihan (opsi).
     */
    public function storeKomposisi(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'tetap' => 'nullable|array',
            'tetap.*.menu_id' => 'required|exists:menu,id',
            'tetap.*.jumlah' => 'required|numeric|gt:0',
            'tetap.*.satuan_sajian' => 'nullable|string|max:50',
            'kelompok' => 'nullable|array',
            'kelompok.*.nama_item' => 'required|string|max:255',
            'kelompok.*.minimum_pilihan' => 'nullable|integer|min:0',
            'kelompok.*.maksimum_pilihan' => 'nullable|integer|min:0',
            'kelompok.*.opsi' => 'nullable|array',
            'kelompok.*.opsi.*.menu_id' => 'required|exists:menu,id',
            'kelompok.*.opsi.*.jumlah' => 'nullable|numeric|gt:0',
            'kelompok.*.opsi.*.satuan_sajian' => 'nullable|string|max:50',
        ]);

        if (empty($validated['tetap']) && empty($validated['kelompok'])) {
            return back()->with('error', 'Komposisi paket minimal memiliki satu item (menu tetap atau kelompok pilihan).');
        }

        try {
            DB::beginTransaction();

            // Cek apakah paket pernah dipesan → jangan ubah kalau sudah terpakai
            $itemPaketIds = $menu->komponen_paket()->pluck('id');
            $dipakai = DB::table('detail_pesanan')->where('menu_id', $menu->id)->exists()
                || DB::table('pilihan_pesanan_catering')->whereIn('item_paket_id', $itemPaketIds)->exists();
            if ($dipakai) {
                DB::rollBack();
                return back()->with('error', 'Komposisi paket tidak dapat diubah karena sudah digunakan dalam transaksi.');
            }

            $menu->komponen_paket()->delete();

            $urutanTetap = 1;
            foreach ($request->tetap ?? [] as $tetap) {
                ItemPaket::create([
                    'menu_id' => $menu->id,
                    'menu_id_terkait' => $tetap['menu_id'],
                    'nama_item' => DB::table('menu')->where('id', $tetap['menu_id'])->value('nama_menu'),
                    'tipe_item' => 'tetap',
                    'jumlah' => $tetap['jumlah'] ?? 1,
                    'satuan_sajian' => $tetap['satuan_sajian'] ?? 'porsi',
                    'urutan' => $urutanTetap++,
                ]);
            }

            $urutanKel = 1;
            foreach ($request->kelompok ?? [] as $kelompok) {
                $kel = ItemPaket::create([
                    'menu_id' => $menu->id,
                    'nama_item' => $kelompok['nama_item'],
                    'tipe_item' => 'pilihan',
                    'minimum_pilihan' => $kelompok['minimum_pilihan'] ?? 1,
                    'maksimum_pilihan' => $kelompok['maksimum_pilihan'] ?? 1,
                    'urutan' => 100 + $urutanKel++,
                ]);

                $urutanOpsi = 1;
                foreach ($kelompok['opsi'] ?? [] as $opsi) {
                    PilihanItemPaket::create([
                        'item_paket_id' => $kel->id,
                        'nama_pilihan' => DB::table('menu')->where('id', $opsi['menu_id'])->value('nama_menu'),
                        'menu_id' => $opsi['menu_id'],
                        'jumlah' => $opsi['jumlah'] ?? 1,
                        'satuan_sajian' => $opsi['satuan_sajian'] ?? 'porsi',
                        'urutan' => $urutanOpsi++,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('resep.index')->with('success', "Komposisi paket {$menu->nama_menu} berhasil disimpan.");

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Terjadi kesalahan saat menyimpan komposisi: '.$e->getMessage());
        }
    }
}
