<?php

namespace App\Http\Controllers;

use App\Support\WhatsAppNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KonsumenProfileController extends Controller
{
    public function edit(): View
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        return view('pelanggan.profile.index', compact('pelanggan'));
    }

    public function update(Request $request): RedirectResponse
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nomor_telepon' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:150'],
            'alamat' => ['nullable', 'string', 'max:255'],
        ]);

        $pelanggan->update([
            'nama' => $request->nama,
            'nomor_telepon' => WhatsAppNumber::normalize($request->nomor_telepon),
            'email' => $request->email,
            'alamat' => $request->alamat,
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $pelanggan = Auth::guard('pelanggan')->user();

        $request->validate([
            'kata_sandi_saat_ini' => $pelanggan->kata_sandi ? 'required|string' : 'nullable|string',
            'kata_sandi' => 'required|string|min:8|confirmed',
        ]);

        // Verifikasi password saat ini sebelum mengganti
        if ($pelanggan->kata_sandi && ! Hash::check($request->kata_sandi_saat_ini, $pelanggan->kata_sandi)) {
            throw ValidationException::withMessages([
                'kata_sandi_saat_ini' => 'Password saat ini salah.',
            ]);
        }

        $pelanggan->update(['kata_sandi' => Hash::make($request->kata_sandi)]);

        return back()->with('success', 'Password berhasil diganti.');
    }
}
