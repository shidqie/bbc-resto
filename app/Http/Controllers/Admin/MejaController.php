<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use Illuminate\Http\Request;

class MejaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mejas = Meja::all()->sortBy(function($m) {
            return (int) preg_replace('/[^0-9]/', '', $m->nomor_meja);
        })->values();

        return view('admin.meja.index', compact('mejas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_meja' => 'required|string|max:50|unique:mejas,nomor_meja',
            'kapasitas'  => 'required|integer|min:1',
            'status'     => 'nullable|in:kosong,menunggu_pembayaran,terisi'
        ]);

        $validated['status'] = $validated['status'] ?? 'kosong';

        Meja::create($validated);

        return redirect()->route('meja.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Meja $meja)
    {
        $validated = $request->validate([
            'nomor_meja' => 'required|string|max:50|unique:mejas,nomor_meja,' . $meja->id,
            'kapasitas'  => 'required|integer|min:1',
            'status'     => 'required|in:kosong,menunggu_pembayaran,terisi'
        ]);

        $meja->update($validated);

        return redirect()->route('meja.index')->with('success', 'Data meja berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Meja $meja)
    {
        if ($meja->status !== 'kosong') {
            return redirect()->route('meja.index')->with('error', 'Meja tidak dapat dihapus karena sedang terisi atau menunggu pembayaran.');
        }

        $meja->delete();

        return redirect()->route('meja.index')->with('success', 'Meja berhasil dihapus.');
    }
}
