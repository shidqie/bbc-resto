<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peran;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Peran::orderBy('id');

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        }

        $roles = $query->paginate(10)->withQueryString();
        
        return view('roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles',
            'description' => 'nullable|string|max:255',
        ]);

        Peran::create($validated);
        return redirect()->route('roles.index')->with('success', 'Hak Akses berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('roles')->ignore($role->id)],
            'description' => 'nullable|string|max:255',
        ]);

        $role->update($validated);
        return redirect()->route('roles.index')->with('success', 'Hak Akses berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->withErrors(['error' => 'Tidak dapat menghapus hak akses yang masih digunakan oleh pengguna.']);
        }
        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Hak Akses berhasil dihapus.');
    }
}
