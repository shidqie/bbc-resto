<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $userRole = auth()->user()->role->name ?? '';

        // Jika peran user ada di dalam daftar roles yang diizinkan (atau Admin punya akses penuh)
        if (in_array($userRole, $roles) || $userRole === 'Admin') {
            return $next($request);
        }

        // Jika tidak memiliki akses
        abort(403, 'Anda tidak memiliki akses ke halaman ini.');
    }
}
