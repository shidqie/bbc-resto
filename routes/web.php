<?php

use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\BuktiPembayaranController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalPengantaranController;
use App\Http\Controllers\KategoriMenuController;
use App\Http\Controllers\LacakPesananController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\MutasiStokController;
use App\Http\Controllers\PaketCateringController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PemasokController;
use App\Http\Controllers\PengadaanController;
use App\Http\Controllers\PenyesuaianStokController;
use App\Http\Controllers\PesananCateringController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PesananNasiBoxController;
use App\Http\Controllers\Pos\DineInController;
use App\Http\Controllers\Pos\DineInPaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrMenuController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StokCateringController;
use App\Http\Controllers\StokOperasionalController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

// ─── PUBLIK — Form Catering ───────────────────────────────────────────────────
Route::get('/pesan/catering', [PesananCateringController::class, 'create'])->name('pesan.catering');
Route::post('/pesan/catering', [PesananCateringController::class, 'store'])->name('pesan.catering.store');
Route::get('/pesan/catering/komponen/{paketId}', [PesananCateringController::class, 'getKomponen'])->name('pesan.catering.komponen');
Route::post('/pesan/catering/preview', [PesananCateringController::class, 'preview'])->name('pesan.catering.preview');

// ─── PUBLIK — QR Self-Order (hanya via QR Code meja) ───────────────────────
Route::get('/qr-scanner', [QrMenuController::class, 'scanner'])->name('qr.scanner');
Route::get('/qr-menu/{token}', [QrMenuController::class, 'index'])->name('qr.menu');
Route::get('/qr-menu', [QrMenuController::class, 'index'])->name('qr.menu.no-token');
Route::post('/qr-menu/order', [QrMenuController::class, 'storeOrder'])->name('qr.menu.order');

// ─── PUBLIK — Form Nasi Box ───────────────────────────────────────────────────
Route::get('/pesan/nasi-box', [PesananNasiBoxController::class, 'create'])->name('pesan.nasibox');
Route::post('/pesan/nasi-box', [PesananNasiBoxController::class, 'store'])->name('pesan.nasibox.store');
Route::post('/pesan/nasi-box/preview', [PesananNasiBoxController::class, 'preview'])->name('pesan.nasibox.preview');

// ─── PUBLIK — Bayar & Status ─────────────────────────────────────────────────
Route::get('/pesan/bayar/{kodePesanan}', [BuktiPembayaranController::class, 'show'])->name('pesanan.bayar');
Route::post('/pesan/bukti', [BuktiPembayaranController::class, 'store'])->name('pesanan.bukti.store');

Route::get('/lacak-pesanan', [LacakPesananController::class, 'index'])->name('lacak.index');
Route::get('/pesan/invoice/{kodePesanan}', [BuktiPembayaranController::class, 'invoicePdf'])->name('pesanan.invoice');

// Legacy redirect agar link lama tidak broken
Route::get('/pesan-catering', function () {
    return redirect()->route('pesan.catering');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/pos/menu/{menu}/toggle-status', [DineInController::class, 'toggleMenuStatus'])->name('pos.menu.toggle-status');

    // ─── MANAJEMEN PENGGUNA & HAK AKSES ───
    Route::middleware(['role:Pemilik,Manajer', 'can:kelola-pengguna'])->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'edit']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });

    Route::middleware(['role:Pemilik,Manajer', 'can:kelola-hak-akses'])->group(function () {
        Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);
    });

    // ─── MASTER DATA (Menu, Bahan Baku, Paket, Meja) ───
    Route::middleware(['role:Admin,Manajer,Pemilik'])->group(function () {
        Route::resource('bahan-baku', BahanBakuController::class);
        Route::resource('kategori-menu', KategoriMenuController::class)->except(['create', 'show', 'edit']);
        Route::patch('/kategori-menu/{kategori_menu}/toggle', [KategoriMenuController::class, 'toggleStatus'])->name('kategori-menu.toggle');
        Route::resource('menu', MenuController::class);
        Route::patch('/menu/{menu}/toggle', [MenuController::class, 'toggleStatus'])->name('menu.toggle');
        Route::get('/resep', [ResepController::class, 'index'])->name('resep.index');
        Route::get('/menu/{menu}/resep/create', [ResepController::class, 'create'])->name('resep.create');
        Route::post('/menu/{menu}/resep', [ResepController::class, 'store'])->name('resep.store');

        Route::resource('paket-catering', PaketCateringController::class);
        Route::patch('/paket-catering/{paketCatering}/toggle', [PaketCateringController::class, 'toggleActive'])->name('paket-catering.toggle');

        Route::post('meja/{meja}/generate-qr', [MejaController::class, 'generateQr'])->name('meja.generate-qr');
        Route::resource('meja', MejaController::class)->except(['create', 'show', 'edit']);
    });

    // ─── STOK BAHAN BAKU & RIWAYAT (View only, semua tim internal) ───
    Route::middleware(['role:Admin,Manajer,Pemilik'])->group(function () {
        Route::get('/mutasi-stok', [MutasiStokController::class, 'index'])->name('mutasi-stok.index');
        Route::get('/stok-operasional', [StokOperasionalController::class, 'index'])->name('stok-operasional.index');
        Route::get('/stok-catering', [StokCateringController::class, 'index'])->name('stok-catering.index');
        // Penyesuaian Stok
        Route::get('/penyesuaian-stok', [PenyesuaianStokController::class, 'index'])->name('penyesuaian-stok.index');
        Route::get('/penyesuaian-stok/create', [PenyesuaianStokController::class, 'create'])->name('penyesuaian-stok.create');
        Route::post('/penyesuaian-stok', [PenyesuaianStokController::class, 'store'])->name('penyesuaian-stok.store');
        Route::get('/penyesuaian-stok/{id}', [PenyesuaianStokController::class, 'show'])->name('penyesuaian-stok.show');
    });

    // ─── PENGADAAN (Pemilik & Manajer) ───
    Route::middleware(['role:Admin,Pemilik,Manajer'])->group(function () {
        // Route spesifik HARUS didaftarkan sebelum resource agar tidak bentrok dengan wildcard {pengadaan}
        Route::get('/pengadaan/terima-barang', [PengadaanController::class, 'terimaBarang'])->name('pengadaan.terima-barang');
        Route::get('/pengadaan/{pengadaan}/pdf', [PengadaanController::class, 'exportPdf'])->name('pengadaan.pdf');
        Route::get('/pengadaan/{pengadaan}/terima', [PengadaanController::class, 'formTerima'])->name('pengadaan.form-terima');
        Route::post('/pengadaan/{pengadaan}/terima', [PengadaanController::class, 'prosesTerima'])->name('pengadaan.proses-terima');
        Route::resource('pengadaan', PengadaanController::class)->except(['edit', 'update', 'destroy']);
    });

    // ─── ADMIN ROUTES (Membutuhkan middleware auth, dll) ───
    Route::middleware(['auth'])->group(function () {

        // ── PESANAN (SEMUA TRANSAKSI) ──
        Route::get('/admin/pesanan', [App\Http\Controllers\Admin\PesananController::class, 'index'])->name('admin.pesanan.index');
        Route::get('/admin/pesanan/detail/{id}', [App\Http\Controllers\Admin\PesananController::class, 'show'])->name('admin.pesanan.show');

    });

    // ─── PESANAN CATERING & NASI BOX (Pemilik) ───
    Route::middleware(['role:Admin,Pemilik'])->group(function () {
        // Pesanan Catering
        Route::get('/admin/pesanan/catering', [PesananCateringController::class, 'index'])->name('admin.pesanan.catering.index');
        Route::get('/admin/pesanan/catering/{pesanan}', [PesananCateringController::class, 'show'])->name('admin.pesanan.catering.show');
        Route::get('/admin/pesanan/catering/{pesanan}/pdf', [PesananCateringController::class, 'exportPdf'])->name('admin.pesanan.catering.pdf');
        Route::patch('/admin/pesanan/catering/{pesanan}/konfirmasi', [PesananCateringController::class, 'konfirmasi'])->name('admin.pesanan.catering.konfirmasi');
        Route::patch('/admin/pesanan/catering/{pesanan}/update-status', [PesananCateringController::class, 'updateStatus'])->name('admin.pesanan.catering.update-status');
        Route::patch('/admin/bukti/{buktiId}/verifikasi-dp', [PesananCateringController::class, 'verifikasiDp'])->name('admin.bukti.verifikasi-dp');

        // Pesanan Nasi Box
        Route::get('/admin/pesanan/nasi-box', [PesananNasiBoxController::class, 'index'])->name('admin.pesanan.nasibox.index');
        Route::get('/admin/pesanan/nasi-box/{pesanan}', [PesananNasiBoxController::class, 'show'])->name('admin.pesanan.nasibox.show');
        Route::get('/admin/pesanan/nasi-box/{pesanan}/pdf', [PesananNasiBoxController::class, 'exportPdf'])->name('admin.pesanan.nasibox.pdf');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/konfirmasi', [PesananNasiBoxController::class, 'konfirmasi'])->name('admin.pesanan.nasibox.konfirmasi');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/update-status', [PesananNasiBoxController::class, 'updateStatus'])->name('admin.pesanan.nasibox.update-status');

        // Jadwal Pengantaran
        Route::get('/admin/jadwal-pengantaran', [JadwalPengantaranController::class, 'index'])->name('admin.jadwal-pengantaran.index');
        Route::patch('/admin/jadwal-pengantaran/{id}/update-status', [JadwalPengantaranController::class, 'updateStatus'])->name('admin.jadwal-pengantaran.update-status');
        Route::get('/admin/jadwal-pengantaran/pdf', [JadwalPengantaranController::class, 'exportPdf'])->name('admin.jadwal-pengantaran.pdf');
    });

    // ─── JADWAL PENGANTARAN ───
    Route::middleware(['role:Admin,Tim Pengantaran'])->group(function () {
        Route::get('/admin/jadwal', [JadwalPengantaranController::class, 'index'])->name('admin.jadwal.index');
        Route::patch('/admin/jadwal/{jenis}/{id}/status', [JadwalPengantaranController::class, 'updateStatus'])->name('admin.jadwal.update-status');
    });

    // ─── LAPORAN ───
    Route::middleware(['role:Admin,Pemilik,Manajer'])->group(function () {
        Route::get('/laporan/stok', [LaporanController::class, 'stok'])->name('laporan.stok');
        Route::get('/laporan/stok/cetak', [LaporanController::class, 'cetakStok'])->name('laporan.stok.cetak');
        Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan/cetak', [LaporanController::class, 'cetakPenjualan'])->name('laporan.penjualan.cetak');
        Route::get('/laporan/pengadaan', [LaporanController::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/pengadaan/cetak', [LaporanController::class, 'cetakPengadaan'])->name('laporan.pengadaan.cetak');
        Route::get('/laporan/menu-terlaris', [LaporanController::class, 'menuTerlaris'])->name('laporan.menu-terlaris');
        Route::get('/laporan/menu-terlaris/cetak', [LaporanController::class, 'cetakMenuTerlaris'])->name('laporan.menu-terlaris.cetak');
    });

    // ─── KASIR & MANAJEMEN (DINE-IN & PESANAN) ───
    // Route yang bisa diakses Kasir, Pemilik, dan Manajer
    Route::middleware(['role:Admin,Kasir,Pemilik,Manajer'])->group(function () {
        Route::get('/pos/dinein/qr-codes', [DineInController::class, 'printQrMeja'])->name('pos.dinein.print-qr');
    });

    Route::middleware(['role:Admin,Kasir,Pemilik,Manajer'])->group(function () {
        // Dine-In Table Management
        Route::get('/pos/dinein', [DineInController::class, 'index'])->name('pos.dinein.index');
        Route::post('/pos/dinein/store', [DineInController::class, 'storePosOrder'])->name('pos.dinein.store-pos');
        Route::patch('/pos/dinein/meja/{meja}/clear', [DineInController::class, 'clearTable'])->name('pos.dinein.clear-table');

        // Dine-In Checkout / Payment
        Route::get('/pos/dinein/meja/{meja}/checkout', [DineInPaymentController::class, 'checkout'])->name('pos.dinein.checkout');
        Route::post('/pos/dinein/meja/{meja}/checkout', [DineInPaymentController::class, 'processPayment'])->name('pos.dinein.processPayment');
        Route::get('/pos/dinein/success/{pesananId}', [DineInPaymentController::class, 'success'])->name('pos.dinein.success');

        // Cetak Struk Dine In & Sub Status Update
        Route::get('/pos/dinein/pesanan/{pesananId}/print-dapur', [DineInController::class, 'printDapur'])->name('pos.dinein.print-dapur');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-meja', [DineInController::class, 'printMeja'])->name('pos.dinein.print-meja');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-gabungan', [DineInController::class, 'printGabungan'])->name('pos.dinein.print-gabungan');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-nota', [DineInController::class, 'printNota'])->name('pos.dinein.print-nota');
        Route::patch('/pos/dinein/pesanan/{pesananId}/sub-status', [DineInController::class, 'updateSubStatus'])->name('pos.dinein.sub-status');
        Route::patch('/pos/dinein/item/{itemId}/toggle-sajian', [DineInController::class, 'toggleStatusSajian'])->name('pos.dinein.toggle-sajian');
        Route::post('/pos/dinein/pesanan/{pesananId}/void', [DineInController::class, 'voidOrder'])->name('pos.dinein.void');

        // Pesanan CRUD (Update Status)
        Route::resource('pesanan', PesananController::class)->except(['create', 'edit', 'update', 'destroy']);
        Route::patch('/pesanan/{pesanan}/status', [PesananController::class, 'updateStatus'])->name('pesanan.update-status');
        Route::get('/pesanan/{pesanan}/cetak/{type}', [PesananController::class, 'cetak'])->name('pesanan.cetak');
    });

    // ─── MIDTRANS PAYMENT API ROUTES ───
    Route::post('/api/payment/qris', [PaymentController::class, 'createQris']);
    Route::get('/api/payment/status/{orderId}', [PaymentController::class, 'checkStatus']);
    Route::post('/api/payment/notification', [PaymentController::class, 'handleNotification']);

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

// Midtrans Webhook
Route::post('/api/midtrans/callback', [MidtransController::class, 'notificationCallback']);

// Alternate Flow 6a: Fallback Polling / Manual Check Route
Route::get('/pesan/check-midtrans-status/{kodePesanan}', [MidtransController::class, 'checkStatusManual'])->name('pesanan.check-midtrans-status');

// Simulasi pembayaran sukses (Dev Mode lokal, pengganti webhook Midtrans)
Route::post('/api/midtrans/localhost-fallback', function (Request $request) {
    $pesanan = Pesanan::with('pembayaran')
        ->where('nomor_pesanan', $request->kode_pesanan)
        ->first();

    if (! $pesanan) {
        return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.']);
    }

    $total = (float) $pesanan->total_tagihan;
    $dpTerbayar = (float) $pesanan->pembayaran->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
    $sisa = max(0, $total - $dpTerbayar);

    if ($sisa <= 0) {
        return response()->json(['success' => false, 'message' => 'Pesanan ini sudah lunas.']);
    }

    Pembayaran::create([
        'nomor_pembayaran' => 'PAY-'.date('YmdHis').'-'.rand(100, 999),
        'pesanan_id' => $pesanan->id,
        'metode_pembayaran_id' => 2, // QRIS
        'status_pembayaran_id' => 3, // LUNAS
        'jenis_pembayaran_id' => $dpTerbayar > 0 ? 3 : 2, // PELUNASAN / UANG_MUKA
        'jumlah_bayar' => $sisa,
        'bukti_pembayaran' => 'midtrans_online',
        'catatan' => 'Simulasi pembayaran sukses (Dev Mode)',
    ]);

    if ($pesanan->status_pesanan_id == 1) {
        $pesanan->update(['status_pesanan_id' => 2]); // DIKONFIRMASI
    }

    return response()->json(['success' => true]);
})->withoutMiddleware([VerifyCsrfToken::class]);

// Routes for Pemasok
Route::middleware(['auth', 'role:Super Admin,Admin Sistem,Manajer,Pemilik'])->group(function () {
    Route::resource('inventory/pemasok', PemasokController::class);
});
