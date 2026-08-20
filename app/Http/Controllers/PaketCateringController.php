<?php

namespace App\Http\Controllers;

use App\Models\ItemPaket;
use App\Models\Menu;
use App\Models\PilihanItemPaket;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class PaketCateringController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->input('jenis', 'catering');
        $query = Menu::with(['komponen_paket.opsi'])->withCount('komponen_paket')->whereHas('komponen_paket');

        if ($jenis === 'catering') {
            $query->where('jenis_menu_id', 2);
        } elseif ($jenis === 'nasi_box') {
            $query->where('jenis_menu_id', 3);
        } else {
            $query->whereIn('jenis_menu_id', [2, 3]);
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where('nama_menu', 'like', '%'.$search.'%');
        }

        $pakets = $query->latest()->get();

        return view('admin.menu.paket.index', compact('pakets', 'jenis'));
    }

    public function create(Request $request)
    {
        $jenis = $request->query('jenis', 'catering');
        $menus = Menu::where('status_aktif', 1)->whereDoesntHave('komponen_paket')->orderBy('nama_menu')->get();

        return view('admin.menu.paket.create', compact('jenis', 'menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jenis_paket' => 'required|in:catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'komponen' => 'required|array|min:1',
            'komponen.*.nama_komponen' => 'nullable|string',
            'komponen.*.menu_id' => 'nullable|exists:menu,id',
            'komponen.*.tipe' => 'required|in:wajib,pilihan,semua_didapat',
            'komponen.*.urutan' => 'required|numeric',
            'komponen.*.jumlah' => 'required|numeric|min:1',
            'komponen.*.pilihan' => 'nullable|array',
            'komponen.*.pilihan.*' => 'string',
            'komponen.*.min_pilihan' => 'nullable|numeric|min:0',
            'komponen.*.max_pilihan' => 'nullable|numeric|min:0',
            'komponen.*.nama_item_manual' => 'nullable|string',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('paket', 'public');
        }

        $jenisId = $request->jenis_paket === 'catering' ? 2 : 3;

        $paket = Menu::create([
            'nama_menu' => $request->nama_paket,
            'id_menu' => 'PKT-'.strtoupper(uniqid()),
            'jenis_menu_id' => $jenisId,
            'kategori_menu_id' => null,
            'harga_jual' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'status_aktif' => 1,
            'foto' => $fotoPath,
        ]);

        foreach ($request->komponen as $komp) {
            $tipe = $komp['tipe'] === 'wajib' ? 'tetap' : ($komp['tipe'] === 'semua_didapat' ? 'semua_didapat' : 'pilihan');
            $menuTerkait = ($tipe === 'tetap' && !empty($komp['menu_id'])) ? Menu::find($komp['menu_id']) : null;
            
            if (!empty($komp['nama_item_manual'])) {
                $namaItem = $komp['nama_item_manual'];
            } elseif ($menuTerkait) {
                $namaItem = $menuTerkait->nama_menu;
            } else {
                $namaItem = $komp['nama_komponen'] ?? 'Pilihan Menu';
            }

            $komponen = ItemPaket::create([
                'menu_id' => $paket->id,
                'menu_id_terkait' => $menuTerkait ? $menuTerkait->id : null,
                'nama_item' => $namaItem,
                'tipe_item' => $tipe,
                'jumlah' => $komp['jumlah'] ?? 1,
                'satuan_sajian' => 'porsi',
                'minimum_pilihan' => $tipe === 'pilihan' ? ($komp['min_pilihan'] ?? 1) : 0,
                'maksimum_pilihan' => $tipe === 'pilihan' ? ($komp['max_pilihan'] ?? 1) : 0,
                'urutan' => $komp['urutan'],
            ]);

            if (($tipe === 'pilihan' || $tipe === 'semua_didapat') && ! empty($komp['pilihan']) && is_array($komp['pilihan'])) {
                $urutanPilihan = 1;
                foreach ($komp['pilihan'] as $pilihanValue) {
                    $pilihanMenu = is_numeric($pilihanValue) ? Menu::find($pilihanValue) : null;
                    PilihanItemPaket::create([
                        'item_paket_id' => $komponen->id,
                        'menu_id' => $pilihanMenu ? $pilihanMenu->id : null,
                        'nama_pilihan' => $pilihanMenu ? $pilihanMenu->nama_menu : $pilihanValue,
                        'jumlah' => $komp['jumlah'] ?? 1,
                        'satuan_sajian' => 'porsi',
                        'urutan' => $urutanPilihan++,
                    ]);
                }
            }
        }

        return redirect()->route('paket-catering.index', ['jenis' => $request->jenis_paket])->with('success', 'Paket berhasil ditambahkan!');
    }

    public function show($id)
    {
        $paketCatering = Menu::with(['komponen_paket.opsi.menu', 'komponen_paket.menu_terkait'])->findOrFail($id);
        
        $kebutuhanBahanService = app(\App\Services\KebutuhanBahanService::class);
        $kebutuhanBahanCollection = $kebutuhanBahanService->kebutuhanMenu($paketCatering, 1);
        
        $kebutuhan = [];
        foreach ($kebutuhanBahanCollection as $item) {
            $bahan = \App\Models\BahanBaku::with('satuan')->find($item['bahan_baku_id']);
            if (! $bahan) {
                continue;
            }
            $kebutuhan[] = [
                'nama_bahan' => $bahan->nama_bahan,
                'satuan' => ($bahan->satuan->nama_satuan ?? '-'),
                'total_kebutuhan' => $item['kebutuhan'],
                'menu_nama' => $item['menu_nama'],
            ];
        }

        return view('admin.menu.paket.show', compact('paketCatering', 'kebutuhan'));
    }

    public function edit($id)
    {
        $paketCatering = Menu::with(['komponen_paket.opsi.menu', 'komponen_paket.menu_terkait'])->findOrFail($id);
        $jenis = $paketCatering->jenis_menu_id == 2 ? 'catering' : 'nasi_box';
        $menus = Menu::where('status_aktif', 1)->whereDoesntHave('komponen_paket')->orderBy('nama_menu')->get();

        return view('admin.menu.paket.edit', compact('paketCatering', 'jenis', 'menus'));
    }

    public function update(Request $request, $id)
    {
        $paketCatering = Menu::findOrFail($id);

        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jenis_paket' => 'required|in:catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'komponen' => 'required|array|min:1',
            'komponen.*.nama_komponen' => 'nullable|string',
            'komponen.*.menu_id' => 'nullable|exists:menu,id',
            'komponen.*.tipe' => 'required|in:wajib,pilihan,semua_didapat',
            'komponen.*.urutan' => 'required|numeric',
            'komponen.*.jumlah' => 'required|numeric|min:1',
            'komponen.*.pilihan' => 'nullable|array',
            'komponen.*.pilihan.*' => 'string',
            'komponen.*.min_pilihan' => 'nullable|numeric|min:0',
            'komponen.*.max_pilihan' => 'nullable|numeric|min:0',
            'komponen.*.nama_item_manual' => 'nullable|string',
        ]);

        $jenisId = $request->jenis_paket === 'catering' ? 2 : 3;

        $data = [
            'nama_menu' => $request->nama_paket,
            'jenis_menu_id' => $jenisId,
            'harga_jual' => $request->harga,
            'deskripsi' => $request->deskripsi,
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('paket', 'public');
        }

        $paketCatering->update($data);

        $paketCatering->komponen_paket()->delete();

        foreach ($request->komponen as $komp) {
            $tipe = $komp['tipe'] === 'wajib' ? 'tetap' : ($komp['tipe'] === 'semua_didapat' ? 'semua_didapat' : 'pilihan');
            $menuTerkait = ($tipe === 'tetap' && !empty($komp['menu_id'])) ? Menu::find($komp['menu_id']) : null;
            
            if (!empty($komp['nama_item_manual'])) {
                $namaItem = $komp['nama_item_manual'];
            } elseif ($menuTerkait) {
                $namaItem = $menuTerkait->nama_menu;
            } else {
                $namaItem = $komp['nama_komponen'] ?? 'Pilihan Menu';
            }

            $komponen = ItemPaket::create([
                'menu_id' => $paketCatering->id,
                'menu_id_terkait' => $menuTerkait ? $menuTerkait->id : null,
                'nama_item' => $namaItem,
                'tipe_item' => $tipe,
                'jumlah' => $komp['jumlah'] ?? 1,
                'satuan_sajian' => 'porsi',
                'minimum_pilihan' => $tipe === 'pilihan' ? ($komp['min_pilihan'] ?? 1) : 0,
                'maksimum_pilihan' => $tipe === 'pilihan' ? ($komp['max_pilihan'] ?? 1) : 0,
                'urutan' => $komp['urutan'],
            ]);

            if (($tipe === 'pilihan' || $tipe === 'semua_didapat') && ! empty($komp['pilihan']) && is_array($komp['pilihan'])) {
                $urutanPilihan = 1;
                foreach ($komp['pilihan'] as $pilihanValue) {
                    $pilihanMenu = is_numeric($pilihanValue) ? Menu::find($pilihanValue) : null;
                    PilihanItemPaket::create([
                        'item_paket_id' => $komponen->id,
                        'menu_id' => $pilihanMenu ? $pilihanMenu->id : null,
                        'nama_pilihan' => $pilihanMenu ? $pilihanMenu->nama_menu : $pilihanValue,
                        'jumlah' => $komp['jumlah'] ?? 1,
                        'satuan_sajian' => 'porsi',
                        'urutan' => $urutanPilihan++,
                    ]);
                }
            }
        }

        return redirect()->route('paket-catering.index', ['jenis' => $request->jenis_paket])->with('success', 'Paket berhasil diperbarui!');
    }

    public function destroy($id)
    {
        try {
            $paketCatering = Menu::findOrFail($id);
            $jenis = $paketCatering->jenis_menu_id == 2 ? 'catering' : 'nasi_box';
            $paketCatering->komponen_paket()->delete();
            $paketCatering->delete();

            return redirect()->route('paket-catering.index', ['jenis' => $jenis])->with('success', 'Paket berhasil dihapus!');
        } catch (QueryException $e) {
            $errorCode = $e->errorInfo[1] ?? $e->getCode();
            if ($errorCode == 1451 || $errorCode == '23000' || strpos($e->getMessage(), '1451') !== false) {
                return redirect()->back()->with('error', 'Menu ini tidak dapat dihapus karena sudah ada di data pesanan. Silakan ubah status menjadi Disembunyikan/Nonaktif.');
            }

            return redirect()->back()->with('error', 'Gagal menghapus menu: Terjadi kesalahan database. '.$e->getMessage());
        }
    }

    public function toggleActive($id)
    {
        $paketCatering = Menu::findOrFail($id);
        $paketCatering->update(['status_aktif' => ! $paketCatering->status_aktif]);

        return redirect()->back()->with('success', 'Status menu diperbarui!');
    }
}
