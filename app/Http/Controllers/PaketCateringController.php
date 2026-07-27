<?php

namespace App\Http\Controllers;

use App\Models\PaketCatering;
use App\Models\DetailPaketCatering;
use App\Models\BahanBaku;
use App\Models\Menu;
use Illuminate\Http\Request;

class PaketCateringController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->input('jenis', 'all');
        $query = PaketCatering::withCount('komponens');

        if ($jenis !== 'all') {
            $query->where('jenis_paket', $jenis);
        }

        $pakets = $query->latest()->get();
        return view('catering.paket.index', compact('pakets', 'jenis'));
    }

    public function create(Request $request)
    {
        $jenis = $request->query('jenis', 'catering');
        $menus = Menu::all();
        return view('catering.paket.create', compact('menus', 'jenis'));
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
            'komponen.*.menu_id' => 'required|array|min:1',
            'komponen.*.menu_id.*' => 'exists:menus,id',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('paket', 'public');
        }

        $paket = PaketCatering::create([
            'nama_paket' => $request->nama_paket,
            'jenis_paket' => $request->jenis_paket,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
        ]);

        foreach ($request->komponen as $komp) {
            $komponen = \App\Models\KomponenPaket::create([
                'paket_catering_id' => $paket->id,
                'nama_komponen' => $komp['nama_komponen'],
                'tipe' => $komp['tipe'],
                'urutan' => $komp['urutan'],
            ]);

            foreach ($komp['menu_id'] as $menuId) {
                \App\Models\OpsiKomponen::create([
                    'komponen_paket_id' => $komponen->id,
                    'menu_id' => $menuId,
                ]);
            }
        }

        return redirect()->route('paket-catering.index', ['jenis' => $request->jenis_paket])->with('success', 'Paket berhasil ditambahkan!');
    }

    public function show(PaketCatering $paketCatering)
    {
        $paketCatering->load('komponens.opsi.menu');
        return view('catering.paket.show', compact('paketCatering'));
    }

    public function edit(PaketCatering $paketCatering)
    {
        $paketCatering->load('komponens.opsi');
        $menus = Menu::all();
        return view('catering.paket.edit', compact('paketCatering', 'menus'));
    }

    public function update(Request $request, PaketCatering $paketCatering)
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
            'komponen.*.menu_id' => 'required|array|min:1',
            'komponen.*.menu_id.*' => 'exists:menus,id',
        ]);

        $data = [
            'nama_paket' => $request->nama_paket,
            'jenis_paket' => $request->jenis_paket,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
        ];

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('paket', 'public');
        }

        $paketCatering->update($data);

        $paketCatering->komponens()->delete();

        foreach ($request->komponen as $komp) {
            $komponen = \App\Models\KomponenPaket::create([
                'paket_catering_id' => $paketCatering->id,
                'nama_komponen' => $komp['nama_komponen'],
                'tipe' => $komp['tipe'],
                'urutan' => $komp['urutan'],
            ]);

            foreach ($komp['menu_id'] as $menuId) {
                \App\Models\OpsiKomponen::create([
                    'komponen_paket_id' => $komponen->id,
                    'menu_id' => $menuId,
                ]);
            }
        }

        return redirect()->route('paket-catering.index', ['jenis' => $paketCatering->jenis_paket])->with('success', 'Paket berhasil diperbarui!');
    }

    public function destroy(PaketCatering $paketCatering)
    {
        $jenis = $paketCatering->jenis_paket;
        $paketCatering->komponens()->delete();
        $paketCatering->delete();
        return redirect()->route('paket-catering.index', ['jenis' => $jenis])->with('success', 'Paket berhasil dihapus!');
    }

    public function toggleActive(PaketCatering $paketCatering)
    {
        $paketCatering->update(['is_active' => !$paketCatering->is_active]);
        return redirect()->back()->with('success', 'Status website diperbarui!');
    }
}
