<?php

namespace App\Http\Controllers;

use App\Models\Pemasok;
use App\Support\WhatsAppNumber;
use Illuminate\Http\Request;

class PemasokController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Pemasok::query();

        if ($search) {
            $query->where('nama_pemasok', 'like', "%{$search}%")
                ->orWhere('kode_pemasok', 'like', "%{$search}%");
        }

        $pemasoks = $query->latest()->paginate(15);

        return view('inventory.pemasok.index', compact('pemasoks', 'search'));
    }

    public function create()
    {
        return view('inventory.pemasok.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_pemasok' => 'required|string|max:150',
            'nomor_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'alamat' => 'nullable|string',
            'nama_kontak' => 'nullable|string|max:100',
        ]);

        $kode = 'SUP-'.str_pad(Pemasok::max('id') + 1, 3, '0', STR_PAD_LEFT);

        Pemasok::create([
            'kode_pemasok' => $kode,
            'nama_pemasok' => $request->nama_pemasok,
            'nomor_telepon' => WhatsAppNumber::normalize($request->nomor_telepon),
            'email' => $request->email,
            'alamat' => $request->alamat,
            'nama_kontak' => $request->nama_kontak,
            'status_aktif' => $request->has('status_aktif') ? 1 : 0,
        ]);

        return redirect()->route('pemasok.index')->with('success', 'Data Pemasok berhasil ditambahkan.');
    }

    public function edit(Pemasok $pemasok)
    {
        return view('inventory.pemasok.edit', compact('pemasok'));
    }

    public function update(Request $request, Pemasok $pemasok)
    {
        $request->validate([
            'nama_pemasok' => 'required|string|max:150',
            'nomor_telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'alamat' => 'nullable|string',
            'nama_kontak' => 'nullable|string|max:100',
        ]);

        $pemasok->update([
            'nama_pemasok' => $request->nama_pemasok,
            'nomor_telepon' => WhatsAppNumber::normalize($request->nomor_telepon),
            'email' => $request->email,
            'alamat' => $request->alamat,
            'nama_kontak' => $request->nama_kontak,
            'status_aktif' => $request->has('status_aktif') ? 1 : 0,
        ]);

        return redirect()->route('pemasok.index')->with('success', 'Data Pemasok berhasil diperbarui.');
    }

    public function destroy(Pemasok $pemasok)
    {
        try {
            $pemasok->delete();

            return back()->with('success', 'Pemasok berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus pemasok karena sudah digunakan pada data lain.');
        }
    }
}
