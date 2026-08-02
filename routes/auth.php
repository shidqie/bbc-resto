<?php

use App\Http\Controllers\Auth\AdminAuthenticatedSessionController;
use App\Http\Controllers\Auth\KonsumenAuthenticatedSessionController;
use App\Http\Controllers\Auth\KonsumenRegisteredUserController;
use App\Http\Controllers\KonsumenPesananController;
use App\Http\Controllers\KonsumenProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    // Admin/Internal Login
    Route::get('admin/login', [AdminAuthenticatedSessionController::class, 'create'])
        ->name('admin.login');

    Route::post('admin/login', [AdminAuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AdminAuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});

// ─── AUTH KONSUMEN (Pelanggan) ───
Route::middleware('guest:pelanggan')->group(function () {
    Route::get('daftar', [KonsumenRegisteredUserController::class, 'create'])
        ->name('konsumen.register');

    Route::post('daftar', [KonsumenRegisteredUserController::class, 'store']);

    Route::get('masuk', [KonsumenAuthenticatedSessionController::class, 'create'])
        ->name('konsumen.login');

    Route::post('masuk', [KonsumenAuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth:pelanggan')->group(function () {
    Route::post('keluar', [KonsumenAuthenticatedSessionController::class, 'destroy'])
        ->name('konsumen.logout');

    Route::get('akun/pesanan', [KonsumenPesananController::class, 'index'])
        ->name('konsumen.pesanan.index');

    Route::get('akun/profile', [KonsumenProfileController::class, 'edit'])
        ->name('konsumen.profile');

    Route::patch('akun/profile', [KonsumenProfileController::class, 'update'])
        ->name('konsumen.profile.update');

    Route::patch('akun/profile/password', [KonsumenProfileController::class, 'updatePassword'])
        ->name('konsumen.profile.password');
});
