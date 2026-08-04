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
        $query = Menu::withCount('komponen_paket')->whereHas('komponen_paket');

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

        return view('menu.paket.index', compact('pakets', 'jenis'));
    }

    public function create(Request $request)
    {
        $jenis = $request->query('jenis', 'catering');

        return view('menu.paket.create', compact('jenis'));
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
            'komponen.*.nama_komponen' => 'required|string',
            'komponen.*.tipe' => 'required|in:fixed,choice',
            'komponen.*.urutan' => 'required|numeric',
            'komponen.*.pilihan' => 'nullable|string', // Comma separated string for nama_pilihan
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('paket', 'public');
        }

        $jenisId = $request->jenis_paket === 'catering' ? 2 : 3;

        $paket = Menu::create([
            'nama_menu' => $request->nama_paket,
            'kode_menu' => 'PKT-'.strtoupper(uniqid()),
            'jenis_menu_id' => $jenisId,
            'kategori_menu_id' => null,
            'harga_jual' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'status_aktif' => 1,
            'foto' => $fotoPath,
        ]);

        foreach ($request->komponen as $komp) {
            $tipe = $komp['tipe'] === 'choice' ? 'pilihan' : 'tetap';
            $komponen = ItemPaket::create([
                'menu_id' => $paket->id,
                'nama_item' => $komp['nama_komponen'],
                'tipe_item' => $tipe,
                'minimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                'maksimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                'urutan' => $komp['urutan'],
            ]);

            if ($tipe === 'pilihan' && ! empty($komp['pilihan'])) {
                $pilihanList = array_map('trim', explode(',', $komp['pilihan']));
                $urutanPilihan = 1;
                foreach ($pilihanList as $namaPilihan) {
                    if (! empty($namaPilihan)) {
                        PilihanItemPaket::create([
                            'item_paket_id' => $komponen->id,
                            'nama_pilihan' => $namaPilihan,
                            'urutan' => $urutanPilihan++,
                        ]);
                    }
                }
            }
        }

        return redirect()->route('paket-catering.index', ['jenis' => $request->jenis_paket])->with('success', 'Paket berhasil ditambahkan!');
    }

    public function show($id)
    {
        $paketCatering = Menu::with('komponen_paket.opsi')->findOrFail($id);

        return view('menu.paket.show', compact('paketCatering'));
    }

    public function edit($id)
    {
        $paketCatering = Menu::with('komponen_paket.opsi')->findOrFail($id);
        $jenis = $paketCatering->jenis_menu_id == 2 ? 'catering' : 'nasi_box';

        return view('menu.paket.edit', compact('paketCatering', 'jenis'));
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
            'komponen.*.nama_komponen' => 'required|string',
            'komponen.*.tipe' => 'required|in:fixed,choice',
            'komponen.*.urutan' => 'required|numeric',
            'komponen.*.pilihan' => 'nullable|string',
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
            $tipe = $komp['tipe'] === 'choice' ? 'pilihan' : 'tetap';
            $komponen = ItemPaket::create([
                'menu_id' => $paketCatering->id,
                'nama_item' => $komp['nama_komponen'],
                'tipe_item' => $tipe,
                'minimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                'maksimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                'urutan' => $komp['urutan'],
            ]);

            if ($tipe === 'pilihan' && ! empty($komp['pilihan'])) {
                $pilihanList = array_map('trim', explode(',', $komp['pilihan']));
                $urutanPilihan = 1;
                foreach ($pilihanList as $namaPilihan) {
                    if (! empty($namaPilihan)) {
                        PilihanItemPaket::create([
                            'item_paket_id' => $komponen->id,
                            'nama_pilihan' => $namaPilihan,
                            'urutan' => $urutanPilihan++,
                        ]);
                    }
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
