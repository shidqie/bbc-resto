<?php

use App\Http\Middleware\CheckRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
        $middleware->redirectGuestsTo(fn ($request) => $request->is('akun/*') || $request->is('lacak-pesanan') || $request->is('pesan/*')
            ? route('konsumen.login')
            : route('admin.login'));
        $middleware->validateCsrfTokens(except: [
            '/api/midtrans/callback',
            '/api/midtrans/localhost-fallback',
            '/api/payment/notification',
            'api/payment/notification',
            '/midtrans/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
