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
        // Register FileUploadService as singleton
        $this->app->singleton(\App\Services\FileUploadService::class, function ($app) {
            return new \App\Services\FileUploadService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Kelola pengguna (CRUD, toggle status, reset password)
        Gate::define('kelola-pengguna', function ($user) {
            return $user->peran && in_array($user->peran->nama_peran, ['Pemilik', 'Manajer', 'Admin Sistem']);
        });

        // Kelola hak akses (roles)
        Gate::define('kelola-hak-akses', function ($user) {
            return $user->peran && in_array($user->peran->nama_peran, ['Pemilik', 'Manajer', 'Admin Sistem']);
        });

        // Hapus pengguna (Pemilik dan Admin Sistem)
        Gate::define('hapus-pengguna', function ($user) {
            return $user->peran && in_array($user->peran->nama_peran, ['Pemilik', 'Admin Sistem']);
        });

        // Ubah pengguna berperan tinggi (Pemilik/Manajer) oleh Pemilik atau Admin Sistem
        Gate::define('ubah-pengguna-prioritas', function ($user) {
            return $user->peran && in_array($user->peran->nama_peran, ['Pemilik', 'Admin Sistem']);
        });
    }
}
