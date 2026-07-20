<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberDashboardController extends Controller
{
    public function index()
    {
        return redirect()->route('member.pesanan.aktif');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('member.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20|unique:users,phone_number,'.$user->id,
            // Email biasanya tidak bisa diubah mudah, tapi kita izinkan
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
        ]);

        $user->update([
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'email' => $request->email,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function alamat()
    {
        $user = Auth::user();
        return view('member.alamat', compact('user'));
    }

    public function updateAlamat(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'alamat' => 'required|string|max:1000',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $user->update([
            'alamat' => $request->alamat,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return back()->with('success', 'Alamat pengiriman default berhasil disimpan!');
    }

    public function password()
    {
        $user = Auth::user();
        return view('member.password', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        return back()->with('success', 'Password berhasil diperbarui!');
    }

    public function pesananAktif()
    {
        $user = Auth::user();
        
        // Ambil pesanan yang belum selesai/batal
        $cateringOrders = \App\Models\PesananCatering::where('user_id', $user->id)
                            ->whereNotIn('status', ['selesai', 'dibatalkan'])
                            ->latest()->get();
                            
        $nasiboxOrders = \App\Models\PesananNasiBox::where('user_id', $user->id)
                            ->whereNotIn('status', ['selesai', 'dibatalkan'])
                            ->latest()->get();
                            
        return view('member.pesanan-aktif', compact('cateringOrders', 'nasiboxOrders'));
    }

    public function riwayat()
    {
        $user = Auth::user();
        
        // Ambil pesanan yang sudah selesai/batal
        $cateringOrders = \App\Models\PesananCatering::where('user_id', $user->id)
                            ->whereIn('status', ['selesai', 'dibatalkan'])
                            ->latest()->get();
                            
        $nasiboxOrders = \App\Models\PesananNasiBox::where('user_id', $user->id)
                            ->whereIn('status', ['selesai', 'dibatalkan'])
                            ->latest()->get();
                            
        return view('member.riwayat', compact('cateringOrders', 'nasiboxOrders'));
    }
}
