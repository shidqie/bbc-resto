<?php

namespace App\Http\Controllers;

use App\Models\PaketCatering;
use App\Models\DetailPaketCatering;
use App\Models\BahanBaku;
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

    public function create()
    {
        $menus = \App\Models\Menu::where('jenis_menu', 'catering')->get();
        return view('catering.paket.create', compact('menus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jenis_paket' => 'required|in:catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'komponen' => 'required|array|min:1',
            'komponen.*.nama_komponen' => 'required|string',
            'komponen.*.tipe' => 'required|in:fixed,choice',
            'komponen.*.urutan' => 'required|numeric',
            'komponen.*.menu_id' => 'required|array|min:1',
            'komponen.*.menu_id.*' => 'exists:menus,id',
        ]);

        $paket = PaketCatering::create([
            'nama_paket' => $request->nama_paket,
            'jenis_paket' => $request->jenis_paket,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
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

        return redirect()->route('paket-catering.index')->with('success', 'Paket berhasil ditambahkan!');
    }

    public function show(PaketCatering $paketCatering)
    {
        $paketCatering->load('komponens.opsi.menu');
        return view('catering.paket.show', compact('paketCatering'));
    }

    public function edit(PaketCatering $paketCatering)
    {
        $paketCatering->load('komponens.opsi');
        $menus = \App\Models\Menu::where('jenis_menu', 'catering')->get();
        return view('catering.paket.edit', compact('paketCatering', 'menus'));
    }

    public function update(Request $request, PaketCatering $paketCatering)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jenis_paket' => 'required|in:catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'komponen' => 'required|array|min:1',
            'komponen.*.nama_komponen' => 'required|string',
            'komponen.*.tipe' => 'required|in:fixed,choice',
            'komponen.*.urutan' => 'required|numeric',
            'komponen.*.menu_id' => 'required|array|min:1',
            'komponen.*.menu_id.*' => 'exists:menus,id',
        ]);

        $paketCatering->update([
            'nama_paket' => $request->nama_paket,
            'jenis_paket' => $request->jenis_paket,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
        ]);

        // Hapus komponen lama
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

        return redirect()->route('paket-catering.index')->with('success', 'Paket berhasil diperbarui!');
    }

    public function destroy(PaketCatering $paketCatering)
    {
        $paketCatering->komponens()->delete();
        $paketCatering->delete();
        return redirect()->route('paket-catering.index')->with('success', 'Paket berhasil dihapus!');
    }

    public function toggleActive(PaketCatering $paketCatering)
    {
        $paketCatering->update(['is_active' => !$paketCatering->is_active]);
        return redirect()->back()->with('success', 'Status paket diperbarui!');
    }
}
