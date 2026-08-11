<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Peran::withCount('pengguna')->orderBy('id');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_peran', 'like', "%{$search}%");
        }

        $roles = $query->paginate(10)->withQueryString();

        return view('admin.pengguna.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_peran' => 'required|string|max:50|unique:peran,nama_peran',
        ]);

        Peran::create($validated);

        return redirect()->route('roles.index')->with('success', 'Hak Akses berhasil ditambahkan.');
    }

    public function update(Request $request, Peran $role)
    {
        $validated = $request->validate([
            'nama_peran' => ['required', 'string', 'max:50', Rule::unique('peran', 'nama_peran')->ignore($role->id)],
        ]);

        $role->update($validated);

        return redirect()->route('roles.index')->with('success', 'Hak Akses berhasil diperbarui.');
    }

    public function destroy(Peran $role)
    {
        if (Pengguna::where('peran_id', $role->id)->exists()) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Tidak dapat menghapus hak akses yang masih digunakan oleh pengguna.']);
        }
        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Hak Akses berhasil dihapus.');
    }
}
