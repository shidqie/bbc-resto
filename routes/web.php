<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('home');

// ─── PUBLIK — Form Catering ───────────────────────────────────────────────────
Route::get('/pesan/catering', [\App\Http\Controllers\PesananCateringController::class, 'create'])->name('pesan.catering');
Route::post('/pesan/catering', [\App\Http\Controllers\PesananCateringController::class, 'store'])->name('pesan.catering.store');
Route::get('/pesan/catering/komponen/{paketId}', [\App\Http\Controllers\PesananCateringController::class, 'getKomponen'])->name('pesan.catering.komponen');
Route::post('/pesan/catering/preview', [\App\Http\Controllers\PesananCateringController::class, 'preview'])->name('pesan.catering.preview');

// ─── PUBLIK — Form Nasi Box ───────────────────────────────────────────────────
Route::get('/pesan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'create'])->name('pesan.nasibox');
Route::post('/pesan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'store'])->name('pesan.nasibox.store');
Route::post('/pesan/nasi-box/preview', [\App\Http\Controllers\PesananNasiBoxController::class, 'preview'])->name('pesan.nasibox.preview');

// ─── PUBLIK — Bayar & Status ─────────────────────────────────────────────────
Route::get('/pesan/bayar/{kodePesanan}', [\App\Http\Controllers\BuktiPembayaranController::class, 'show'])->name('pesanan.bayar');
Route::post('/pesan/bukti', [\App\Http\Controllers\BuktiPembayaranController::class, 'store'])->name('pesanan.bukti.store');
Route::get('/pesan/status/{kodePesanan}', [\App\Http\Controllers\BuktiPembayaranController::class, 'status'])->name('pesanan.status');

// Legacy redirect agar link lama tidak broken
Route::get('/pesan-catering', function() { return redirect()->route('pesan.catering'); });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Route Khusus Admin
    Route::middleware(['role:Admin'])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->except(['create', 'show', 'edit']);
        Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('products', \App\Http\Controllers\ProductController::class)->except(['create', 'show', 'edit']);
    });

    // Route Manajer (Gudang, Menu & Semua Laporan) - Admin otomatis bisa akses
    Route::middleware(['role:Manajer'])->group(function () {
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

        // Laporan Stok (Hanya Manajer & Admin)
        Route::get('/laporan/stok', [\App\Http\Controllers\LaporanController::class, 'stok'])->name('laporan.stok');
        Route::get('/laporan/stok/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakStok'])->name('laporan.stok.cetak');

        // Paket Catering & Nasi Box CRUD
        Route::resource('paket-catering', \App\Http\Controllers\PaketCateringController::class);
        Route::patch('/paket-catering/{paketCatering}/toggle', [\App\Http\Controllers\PaketCateringController::class, 'toggleActive'])->name('paket-catering.toggle');

        // Manajemen Pesanan Catering (Admin — new schema)
        Route::get('/admin/pesanan/catering', [\App\Http\Controllers\PesananCateringController::class, 'index'])->name('admin.pesanan.catering.index');
        Route::get('/admin/pesanan/catering/{pesanan}', [\App\Http\Controllers\PesananCateringController::class, 'show'])->name('admin.pesanan.catering.show');
        Route::patch('/admin/pesanan/catering/{pesanan}/konfirmasi', [\App\Http\Controllers\PesananCateringController::class, 'konfirmasi'])->name('admin.pesanan.catering.konfirmasi');
        Route::patch('/admin/bukti/{buktiId}/verifikasi-dp', [\App\Http\Controllers\PesananCateringController::class, 'verifikasiDp'])->name('admin.bukti.verifikasi-dp');

        // Manajemen Pesanan Nasi Box (Admin)
        Route::get('/admin/pesanan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'index'])->name('admin.pesanan.nasibox.index');
        Route::get('/admin/pesanan/nasi-box/{pesanan}', [\App\Http\Controllers\PesananNasiBoxController::class, 'show'])->name('admin.pesanan.nasibox.show');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/konfirmasi', [\App\Http\Controllers\PesananNasiBoxController::class, 'konfirmasi'])->name('admin.pesanan.nasibox.konfirmasi');

    });

    // Route Kasir (POS, Pembayaran, Laporan Penjualan) - Admin & Manajer otomatis bisa akses (karena Manajer butuh laporan penjualan juga)
    // TAPI tunggu, Kasir itu role tersendiri. Manajer tidak punya akses ke Kasir routes secara default kecuali kita tambahkan.
    // Solusi: Buat group gabungan untuk Laporan Penjualan yang bisa diakses Kasir dan Manajer.
    Route::middleware(['role:Kasir,Manajer'])->group(function () {
        Route::get('/laporan/penjualan', [\App\Http\Controllers\LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakPenjualan'])->name('laporan.penjualan.cetak');
    });

    Route::middleware(['role:Kasir'])->group(function () {
        Route::resource('pesanan', \App\Http\Controllers\PesananController::class)->except(['edit', 'update', 'destroy']);
        Route::patch('/pesanan/{pesanan}/status', [\App\Http\Controllers\PesananController::class, 'updateStatus'])
            ->name('pesanan.update-status');
    });

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
