<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'pegawai');
        
        $query = Pengguna::with('role')->orderBy('dibuat_pada', 'desc');

        if ($type === 'pelanggan') {
            // Only Konsumen
            $query->whereHas('role', function($q) {
                $q->where('name', 'Konsumen');
            });
            $pageTitle = 'Data Pelanggan';
            $pageDescription = 'Kelola akun pelanggan yang terdaftar di sistem.';
        } else {
            // pegawai: all except Konsumen
            $query->whereHas('role', function($q) {
                $q->where('name', '!=', 'Konsumen');
            });
            $pageTitle = 'Data Pegawai';
            $pageDescription = 'Kelola akun staf, kasir, dan admin.';
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();
        
        // Roles for the create/edit modal
        $roles = Peran::orderBy('id')->get();
        
        return view('users.index', compact('users', 'roles', 'type', 'pageTitle', 'pageDescription'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone_number' => 'nullable|string|max:20',
            'peran_id' => 'required|exists:peran,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        Pengguna::create($validated);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone_number' => 'nullable|string|max:20',
            'peran_id' => 'required|exists:peran,id',
        ]);

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8']);
            $validated['password'] = Hash::make($request->kata_sandi);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
