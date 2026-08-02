<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Support\WhatsAppNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class KonsumenRegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.konsumen-register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:150'],
            'nomor_telepon' => ['required', 'string', 'regex:/^(\+?62|0|8)\d{8,13}$/'],
            'kata_sandi' => ['required', 'string', 'confirmed', Rules\Password::min(8)],
        ]);

        $nomorTelepon = WhatsAppNumber::normalize($request->nomor_telepon);

        // Jika pelanggan sudah pernah memesan tanpa akun (guest order),
        // akun dibuat dengan mengikat data yang sama agar pesanan lamanya otomatis terhubung.
        $query = Pelanggan::where('nomor_telepon', $nomorTelepon);
        if ($request->filled('email')) {
            $query->orWhere('email', $request->email);
        }
        $pelanggan = $query->first();

        if ($pelanggan) {
            $pelanggan->update([
                'kata_sandi' => Hash::make($request->kata_sandi),
                'email' => $request->email ?: $pelanggan->email,
            ]);
        } else {
            $pelanggan = Pelanggan::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'nomor_telepon' => $nomorTelepon,
                'kata_sandi' => Hash::make($request->kata_sandi),
            ]);
        }

        Auth::guard('pelanggan')->login($pelanggan);
        $request->session()->regenerate();

        return redirect()->route('konsumen.pesanan.index');
    }
}
