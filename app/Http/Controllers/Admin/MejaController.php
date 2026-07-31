<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MejaController extends Controller
{
    public function index(Request $request)
    {
        $query = Meja::query();
        
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nomor_meja', 'like', "%{$search}%");
        }

        $mejas = $query->orderBy(DB::raw('CAST(REGEXP_REPLACE(nomor_meja, "[^0-9]", "") AS UNSIGNED)'), 'asc')
                       ->paginate(10)
                       ->withQueryString();

        return view('pos.meja.index', compact('mejas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_meja' => 'required|string|max:50|unique:meja,nomor_meja',
            'kapasitas'  => 'required|integer|min:1',
            'area'       => 'nullable|string|max:50',
            'status_meja_id' => 'nullable|integer|in:1,2,4'
        ]);

        if (!isset($validated['status_meja_id'])) {
            $validated['status_meja_id'] = 1;
        }

        Meja::create($validated);

        return back()->with('success', 'Meja berhasil ditambahkan.');
    }

    public function update(Request $request, Meja $meja)
    {
        $validated = $request->validate([
            'nomor_meja' => 'required|string|max:50|unique:meja,nomor_meja,' . $meja->id,
            'kapasitas'  => 'required|integer|min:1',
            'area'       => 'nullable|string|max:50',
            'status_meja_id' => 'nullable|integer|in:1,2,4'
        ]);

        if (!isset($validated['status_meja_id'])) {
            $validated['status_meja_id'] = $meja->status_meja_id;
        }

        $meja->update($validated);

        return back()->with('success', 'Data meja berhasil diperbarui.');
    }

    public function generateQr(Meja $meja)
    {
        $meja->qr_token = \Illuminate\Support\Str::random(32);
        
        if (empty($meja->kode_meja)) {
            $number = preg_replace('/[^0-9]/', '', $meja->nomor_meja);
            if ($number) {
                $meja->kode_meja = 'MJ-' . str_pad($number, 3, '0', STR_PAD_LEFT);
            } else {
                $meja->kode_meja = 'MJ-' . strtoupper(\Illuminate\Support\Str::random(4));
            }
        }
        
        $meja->save();

        return back()->with('success', 'QR Token berhasil di-generate untuk ' . $meja->nomor_meja);
    }

    public function destroy(Meja $meja)
    {
        if ($meja->status_meja_id != 1) { // 1 = TERSEDIA
            return back()->with('error', 'Meja tidak dapat dihapus karena sedang terisi atau dipesan.');
        }

        $meja->delete();

        return back()->with('success', 'Meja berhasil dihapus.');
    }
}
