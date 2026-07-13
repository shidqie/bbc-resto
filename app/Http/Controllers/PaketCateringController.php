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
        $query = PaketCatering::withCount('detailBahan');

        if ($jenis !== 'all') {
            $query->where('jenis_paket', $jenis);
        }

        $pakets = $query->latest()->get();
        return view('catering.paket.index', compact('pakets', 'jenis'));
    }

    public function create()
    {
        $bahanBakus = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();
        return view('catering.paket.create', compact('bahanBakus'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jenis_paket' => 'required|in:catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'exists:bahan_bakus,id',
            'jumlah_kebutuhan' => 'required|array',
            'jumlah_kebutuhan.*' => 'required|numeric|min:0.01',
        ]);

        $paket = PaketCatering::create([
            'nama_paket' => $request->nama_paket,
            'jenis_paket' => $request->jenis_paket,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
        ]);

        foreach ($request->bahan_baku_id as $index => $bahanBakuId) {
            DetailPaketCatering::create([
                'paket_catering_id' => $paket->id,
                'bahan_baku_id' => $bahanBakuId,
                'jumlah_kebutuhan' => $request->jumlah_kebutuhan[$index],
            ]);
        }

        return redirect()->route('paket-catering.index')->with('success', 'Paket berhasil ditambahkan!');
    }

    public function show(PaketCatering $paketCatering)
    {
        $paketCatering->load('detailBahan.bahanBaku.satuan');
        return view('catering.paket.show', compact('paketCatering'));
    }

    public function edit(PaketCatering $paketCatering)
    {
        $paketCatering->load('detailBahan');
        $bahanBakus = BahanBaku::with('satuan')->orderBy('nama_bahan')->get();
        return view('catering.paket.edit', compact('paketCatering', 'bahanBakus'));
    }

    public function update(Request $request, PaketCatering $paketCatering)
    {
        $request->validate([
            'nama_paket' => 'required|string|max:255',
            'jenis_paket' => 'required|in:catering,nasi_box',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'bahan_baku_id' => 'required|array|min:1',
            'bahan_baku_id.*' => 'exists:bahan_bakus,id',
            'jumlah_kebutuhan' => 'required|array',
            'jumlah_kebutuhan.*' => 'required|numeric|min:0.01',
        ]);

        $paketCatering->update([
            'nama_paket' => $request->nama_paket,
            'jenis_paket' => $request->jenis_paket,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
        ]);

        // Hapus BOM lama dan buat ulang
        $paketCatering->detailBahan()->delete();
        foreach ($request->bahan_baku_id as $index => $bahanBakuId) {
            DetailPaketCatering::create([
                'paket_catering_id' => $paketCatering->id,
                'bahan_baku_id' => $bahanBakuId,
                'jumlah_kebutuhan' => $request->jumlah_kebutuhan[$index],
            ]);
        }

        return redirect()->route('paket-catering.index')->with('success', 'Paket berhasil diperbarui!');
    }

    public function destroy(PaketCatering $paketCatering)
    {
        $paketCatering->detailBahan()->delete();
        $paketCatering->delete();
        return redirect()->route('paket-catering.index')->with('success', 'Paket berhasil dihapus!');
    }

    public function toggleActive(PaketCatering $paketCatering)
    {
        $paketCatering->update(['is_active' => !$paketCatering->is_active]);
        return redirect()->back()->with('success', 'Status paket diperbarui!');
    }
}
