<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use App\Models\Peran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $roleFilter = $request->get('role', '');
        $statusFilter = $request->get('status', '');

        // Pengguna Internal (exclude Pelanggan)
        $penggunaQuery = Pengguna::with('peran')
            ->whereHas('peran', function ($q) {
                $q->where('nama_peran', '!=', 'Pelanggan');
            })
            ->orderBy('dibuat_pada', 'desc');

        // Data Pelanggan
        $pelangganQuery = Pengguna::with('peran')
            ->whereHas('peran', function ($q) {
                $q->where('nama_peran', 'Pelanggan');
            })
            ->orderBy('dibuat_pada', 'desc');

        if ($search !== '') {
            $penggunaQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%");
            });
            $pelangganQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('nomor_telepon', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== '') {
            $penggunaQuery->whereHas('peran', function ($q) use ($roleFilter) {
                $q->where('nama_peran', $roleFilter);
            });
        }

        if ($statusFilter !== '') {
            $penggunaQuery->where('status_aktif', $statusFilter === 'aktif');
            $pelangganQuery->where('status_aktif', $statusFilter === 'aktif');
        }

        $pengguna = $penggunaQuery->paginate(10)->withQueryString();
        $pelanggan = $pelangganQuery->paginate(10, ['*'], 'pelanggan_page')->withQueryString();

        // Roles for the create/edit modal (exclude Pelanggan for internal users)
        $roles = Peran::where('nama_peran', '!=', 'Pelanggan')->orderBy('id')->get();

        return view('users.index', compact('pengguna', 'pelanggan', 'roles', 'search', 'roleFilter', 'statusFilter'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:pengguna,email',
            'password' => 'required|string|min:8|confirmed',
            'nomor_telepon' => 'nullable|string|max:20',
            'peran_id' => 'required|exists:peran,id',
            'status_aktif' => 'boolean',
        ]);

        Pengguna::create([
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'kata_sandi' => Hash::make($validated['password']),
            'nomor_telepon' => $validated['nomor_telepon'] ?? null,
            'peran_id' => $validated['peran_id'],
            'status_aktif' => $validated['status_aktif'] ?? true,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function show(Pengguna $user)
    {
        $user->load('peran');
        
        // Get related data for detail view
        $pesananCount = 0;
        $pesananDineIn = collect();
        $pesananCatering = collect();
        
        if ($user->isPelanggan()) {
            // For customers, we might have related Pelanggan record
            $pelanggan = \App\Models\Pelanggan::where('email', $user->email)->first();
            if ($pelanggan) {
                $pesananCount = $pelanggan->pesanan()->count();
                $pesananDineIn = $pelanggan->pesanan()->whereHas('jenisPesanan', function($q) {
                    $q->where('kode_jenis', 'dine_in');
                })->latest()->take(5)->get();
                $pesananCatering = $pelanggan->pesanan()->whereHas('jenisPesanan', function($q) {
                    $q->whereIn('kode_jenis', ['catering', 'nasi_box']);
                })->latest()->take(5)->get();
            }
        } else {
            // For internal users
            $pesananCount = $user->pesananSebagaiPelayan()->count() + $user->pesananSebagaiKasir()->count();
            $pesananDineIn = $user->pesananSebagaiPelayan()->latest()->take(5)->get();
        }

        return view('users.show', compact('user', 'pesananCount', 'pesananDineIn', 'pesananCatering'));
    }

    public function update(Request $request, Pengguna $user)
    {
        // Prevent users from deactivating themselves
        if ($user->id === auth()->id() && $request->has('status_aktif') && !$request->boolean('status_aktif')) {
            return redirect()->back()->withErrors(['status_aktif' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.']);
        }

        // Prevent non-Pemilik from modifying Pemilik or Manajer
        $currentUser = auth()->user();
        if (!$currentUser->isPemilik() && ($user->isPemilik() || $user->isManajer())) {
            abort(403, 'Anda tidak memiliki izin untuk mengubah pengguna dengan peran Pemilik atau Manajer.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('pengguna', 'email')->ignore($user->id)],
            'nomor_telepon' => 'nullable|string|max:20',
            'peran_id' => 'required|exists:peran,id',
            'status_aktif' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = [
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'nomor_telepon' => $validated['nomor_telepon'] ?? null,
            'peran_id' => $validated['peran_id'],
            'status_aktif' => $validated['status_aktif'] ?? true,
        ];

        if ($request->filled('password')) {
            $data['kata_sandi'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleStatus(Pengguna $user)
    {
        $currentUser = auth()->user();

        // Prevent users from deactivating themselves
        if ($user->id === $currentUser->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak bisa menonaktifkan akun Anda sendiri.'
            ], 403);
        }

        // Prevent non-Pemilik from modifying Pemilik or Manajer
        if (!$currentUser->isPemilik() && ($user->isPemilik() || $user->isManajer())) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengubah status pengguna dengan peran Pemilik atau Manajer.'
            ], 403);
        }

        // Ensure at least one Pemilik remains active
        if ($user->isPemilik() && !$user->status_aktif) {
            $activePemilikCount = Pengguna::whereHas('peran', function($q) {
                $q->where('nama_peran', 'Pemilik');
            })->where('status_aktif', true)->count();
            
            if ($activePemilikCount <= 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Minimal satu akun Pemilik harus tetap aktif.'
                ], 403);
            }
        }

        $user->update(['status_aktif' => !$user->status_aktif]);

        return response()->json([
            'success' => true,
            'message' => 'Status akun berhasil ' . ($user->status_aktif ? 'diaktifkan' : 'dinonaktifkan') . '.',
            'status_aktif' => $user->status_aktif
        ]);
    }

    public function resetPassword(Request $request, Pengguna $user)
    {
        $currentUser = auth()->user();

        // Prevent non-Pemilik from resetting password for Pemilik or Manajer
        if (!$currentUser->isPemilik() && ($user->isPemilik() || $user->isManajer())) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki izin untuk mengatur ulang kata sandi pengguna dengan peran Pemilik atau Manajer.'
            ], 403);
        }

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user->update([
            'kata_sandi' => Hash::make($request->password)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi berhasil diatur ulang.'
        ]);
    }

    public function destroy(Pengguna $user)
    {
        $this->authorize('hapus-pengguna');

        $currentUser = auth()->user();

        // Prevent users from deleting themselves
        if ($user->id === $currentUser->id) {
            return redirect()->route('users.index')->withErrors(['error' => 'Anda tidak bisa menghapus akun Anda sendiri.']);
        }

        // Prevent non-Pemilik from deleting Pemilik or Manajer
        if (!$currentUser->isPemilik() && ($user->isPemilik() || $user->isManajer())) {
            return redirect()->route('users.index')->withErrors(['error' => 'Anda tidak memiliki izin untuk menghapus pengguna dengan peran Pemilik atau Manajer.']);
        }

        // Check if user has related transactions
        $hasTransactions = $user->pesananSebagaiPelayan()->exists() 
            || $user->pesananSebagaiKasir()->exists()
            || $user->pengadaanDiajukan()->exists()
            || $user->pengadaanDisetujui()->exists()
            || $user->penerimaanBahan()->exists()
            || $user->mutasiStok()->exists()
            || $user->penyesuaianStok()->exists()
            || $user->pengantaran()->exists()
            || $user->pembayaranDiproses()->exists();

        if ($hasTransactions) {
            // Soft delete by deactivating instead of hard delete
            $user->update(['status_aktif' => false]);
            return redirect()->route('users.index')->with('success', 'Pengguna memiliki data transaksi, akun dinonaktifkan.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
