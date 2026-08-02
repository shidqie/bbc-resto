<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Kelola pengguna (CRUD, toggle status, reset password)
        Gate::define('kelola-pengguna', function ($user) {
            return $user->peran && in_array($user->peran->nama_peran, ['Pemilik', 'Manajer']);
        });

        // Kelola hak akses (roles)
        Gate::define('kelola-hak-akses', function ($user) {
            return $user->peran && in_array($user->peran->nama_peran, ['Pemilik', 'Manajer']);
        });

        // Hapus pengguna (hanya Pemilik)
        Gate::define('hapus-pengguna', function ($user) {
            return $user->peran && $user->peran->nama_peran === 'Pemilik';
        });

        // Ubah pengguna berperan tinggi (Pemilik/Manajer) hanya oleh Pemilik
        Gate::define('ubah-pengguna-prioritas', function ($user) {
            return $user->peran && $user->peran->nama_peran === 'Pemilik';
        });
    }
}
