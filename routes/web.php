<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    

    Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
    Route::resource('roles', \App\Http\Controllers\RoleController::class)->except(['create', 'show', 'edit']);
    Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except(['create', 'show', 'edit']);
    Route::resource('products', \App\Http\Controllers\ProductController::class)->except(['create', 'show', 'edit']);
    Route::resource('bahan-baku', \App\Http\Controllers\BahanBakuController::class);
    Route::get('/mutasi-stok', [\App\Http\Controllers\MutasiStokController::class, 'index'])->name('mutasi-stok.index');
    Route::get('/stok-menipis', [\App\Http\Controllers\StokMenipisController::class, 'index'])->name('stok-menipis.index');
    // Pengadaan Routes
    Route::resource('pengadaan', \App\Http\Controllers\PengadaanController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('/pengadaan/{pengadaan}/status', [\App\Http\Controllers\PengadaanController::class, 'updateStatus'])->name('pengadaan.update-status');
    
    // Menu & Resep Routes
    Route::resource('kategori-menu', \App\Http\Controllers\KategoriMenuController::class)->except(['create', 'show', 'edit']);
    Route::resource('menu', \App\Http\Controllers\MenuController::class);
    Route::get('/menu/{menu}/resep/create', [\App\Http\Controllers\ResepController::class, 'create'])->name('resep.create');
    Route::post('/menu/{menu}/resep', [\App\Http\Controllers\ResepController::class, 'store'])->name('resep.store');
    
    // Orders Management Routes
    // Pesanan Management Routes
    Route::resource('pesanan', \App\Http\Controllers\PesananController::class)->except(['edit', 'update', 'destroy']);
    Route::patch('/pesanan/{pesanan}/status', [\App\Http\Controllers\PesananController::class, 'updateStatus'])
        ->name('pesanan.update-status');

    // Keep dummy route for other dummy pages
    Route::get('/dummy/{page}', function ($page) {
        $titles = [
            'daftar-menu' => 'Daftar Menu',
            'kategori-menu' => 'Kategori Menu',
            'tambah-menu' => 'Tambah Menu',
            'menu-tidak-aktif' => 'Menu Tidak Aktif',
            'semua-transaksi' => 'Semua Transaksi Pembayaran',
            'cash-qris' => 'Pembayaran Cash / QRIS',
            'dp-pelunasan' => 'DP & Pelunasan',
            'daftar-bahan-baku' => 'Daftar Bahan Baku',
            'stok-masuk-keluar' => 'Stok Masuk / Keluar',
            'stok-menipis' => 'Stok Menipis',
            'pengadaan-harian' => 'Pengadaan Harian',
            'pengadaan-pesanan' => 'Pengadaan Pesanan',
            'riwayat-pengadaan' => 'Riwayat Pengadaan',
            'laporan' => 'Laporan & Statistik',
            'data-pengguna' => 'Data Pengguna',
            'hak-akses' => 'Hak Akses Pengguna',
            'profil-usaha' => 'Profil Usaha',
            'metode-pembayaran' => 'Pengaturan Metode Pembayaran',
            'cetak-struk' => 'Pengaturan Cetak Struk',
        ];
        $title = $titles[$page] ?? ucfirst(str_replace('-', ' ', $page));
        return view('dummy', compact('title'));
    })->name('dummy');
});

require __DIR__.'/auth.php';
