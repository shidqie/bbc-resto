<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pelanggan;
use App\Support\WhatsAppNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class KonsumenAuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.konsumen-login');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'kata_sandi' => ['required', 'string'],
        ]);

        $login = $request->input('login');
        $pelanggan = Pelanggan::where('email', $login)
            ->orWhere('nomor_telepon', $login)
            ->orWhere('nomor_telepon', WhatsAppNumber::normalize($login))
            ->first();

        if (! $pelanggan || ! $pelanggan->kata_sandi || ! Hash::check($request->kata_sandi, $pelanggan->kata_sandi)) {
            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        Auth::guard('pelanggan')->login($pelanggan, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('konsumen.pesanan.index'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('pelanggan')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
