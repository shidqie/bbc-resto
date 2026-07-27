<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class AdminAuthenticatedSessionController extends Controller
{
    /**
     * Display the login view for internal (admin/staff).
     */
    public function create(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        
        $user = Auth::user();
        $role = $user->role->name ?? '';

        // Tolak jika Konsumen mencoba masuk ke portal admin
        if ($role === 'Konsumen') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'login' => 'Akun Anda tidak memiliki akses ke Portal Internal.',
            ]);
        }

        $request->session()->regenerate();

        if ($role === 'Kasir') {
            return redirect()->intended(route('pos.dinein.index', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
