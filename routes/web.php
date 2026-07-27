<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('home');

// ─── PUBLIK — Form Catering ───────────────────────────────────────────────────
Route::get('/pesan/catering', [\App\Http\Controllers\PesananCateringController::class, 'create'])->name('pesan.catering');
Route::post('/pesan/catering', [\App\Http\Controllers\PesananCateringController::class, 'store'])->name('pesan.catering.store');
Route::get('/pesan/catering/komponen/{paketId}', [\App\Http\Controllers\PesananCateringController::class, 'getKomponen'])->name('pesan.catering.komponen');
Route::post('/pesan/catering/preview', [\App\Http\Controllers\PesananCateringController::class, 'preview'])->name('pesan.catering.preview');

// ─── PUBLIK — QR Menu & Scan ───────────────────────────────────────────────────
Route::get('/qr-menu', [\App\Http\Controllers\QrMenuController::class, 'index'])->name('qr.menu');
Route::get('/menu/scan', [\App\Http\Controllers\QrMenuController::class, 'index'])->name('menu.scan');
Route::get('/scan-qr', [\App\Http\Controllers\QrMenuController::class, 'scanner'])->name('qr.scanner');
Route::post('/qr-menu/order', [\App\Http\Controllers\QrMenuController::class, 'storeOrder'])->name('qr.menu.order');
Route::post('/qr-menu/panggil-pelayan', [\App\Http\Controllers\QrMenuController::class, 'panggilPelayan'])->name('qr.menu.panggil-pelayan');

// ─── PUBLIK — Form Nasi Box ───────────────────────────────────────────────────
Route::get('/pesan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'create'])->name('pesan.nasibox');
Route::post('/pesan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'store'])->name('pesan.nasibox.store');
Route::post('/pesan/nasi-box/preview', [\App\Http\Controllers\PesananNasiBoxController::class, 'preview'])->name('pesan.nasibox.preview');

// ─── PUBLIK — Bayar & Status ─────────────────────────────────────────────────
Route::get('/pesan/bayar/{kodePesanan}', [\App\Http\Controllers\BuktiPembayaranController::class, 'show'])->name('pesanan.bayar');
Route::post('/pesan/bukti', [\App\Http\Controllers\BuktiPembayaranController::class, 'store'])->name('pesanan.bukti.store');
Route::get('/pesan/status/{kodePesanan}', [\App\Http\Controllers\BuktiPembayaranController::class, 'status'])->name('pesanan.status');
Route::get('/pesan/invoice/{kodePesanan}', [\App\Http\Controllers\BuktiPembayaranController::class, 'invoicePdf'])->name('pesanan.invoice');

// Legacy redirect agar link lama tidak broken
Route::get('/pesan-catering', function() { return redirect()->route('pesan.catering'); });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/pos/menu/{menu}/toggle-status', [\App\Http\Controllers\Pos\DineInController::class, 'toggleMenuStatus'])->name('pos.menu.toggle-status');
    
    // Route Khusus Konsumen
    Route::middleware(['role:Konsumen'])->group(function () {
        Route::get('/member/dashboard', [\App\Http\Controllers\MemberDashboardController::class, 'index'])->name('member.dashboard');
        
        // Portal Member Baru
        Route::get('/member/profile', [\App\Http\Controllers\MemberDashboardController::class, 'profile'])->name('member.profile');
        Route::patch('/member/profile', [\App\Http\Controllers\MemberDashboardController::class, 'updateProfile'])->name('member.profile.update');
        Route::get('/member/alamat', [\App\Http\Controllers\MemberDashboardController::class, 'alamat'])->name('member.alamat');
        Route::patch('/member/alamat', [\App\Http\Controllers\MemberDashboardController::class, 'updateAlamat'])->name('member.alamat.update');
        Route::get('/member/password', [\App\Http\Controllers\MemberDashboardController::class, 'password'])->name('member.password');
        Route::patch('/member/password', [\App\Http\Controllers\MemberDashboardController::class, 'updatePassword'])->name('member.password.update');
        Route::get('/member/pesanan-aktif', [\App\Http\Controllers\MemberDashboardController::class, 'pesananAktif'])->name('member.pesanan.aktif');
        Route::get('/member/riwayat', [\App\Http\Controllers\MemberDashboardController::class, 'riwayat'])->name('member.pesanan.riwayat');
    });

    // Route Khusus Admin
    Route::middleware(['role:Admin'])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->except(['create', 'show', 'edit']);
        Route::resource('categories', \App\Http\Controllers\CategoryController::class)->except(['create', 'show', 'edit']);
        Route::resource('products', \App\Http\Controllers\ProductController::class)->except(['create', 'show', 'edit']);
    });

    // ─── MASTER DATA (Menu, Bahan Baku, Paket) ───
    Route::middleware(['role:Manajer,Pemilik'])->group(function () {
        Route::resource('bahan-baku', \App\Http\Controllers\BahanBakuController::class);
        Route::resource('kategori-menu', \App\Http\Controllers\KategoriMenuController::class)->except(['create', 'show', 'edit']);
        Route::resource('menu', \App\Http\Controllers\MenuController::class);
        Route::patch('/menu/{menu}/toggle', [\App\Http\Controllers\MenuController::class, 'toggleStatus'])->name('menu.toggle');
        Route::get('/menu/{menu}/resep/create', [\App\Http\Controllers\ResepController::class, 'create'])->name('resep.create');
        Route::post('/menu/{menu}/resep', [\App\Http\Controllers\ResepController::class, 'store'])->name('resep.store');
        
        Route::resource('paket-catering', \App\Http\Controllers\PaketCateringController::class);
        Route::patch('/paket-catering/{paketCatering}/toggle', [\App\Http\Controllers\PaketCateringController::class, 'toggleActive'])->name('paket-catering.toggle');
    });

    // ─── PENGGUNAAN BAHAN BAKU HARIAN ───
    Route::middleware(['role:Manajer,Tim Dapur'])->group(function () {
        Route::get('/mutasi-stok', [\App\Http\Controllers\MutasiStokController::class, 'index'])->name('mutasi-stok.index');
        Route::get('/stok-menipis', [\App\Http\Controllers\StokMenipisController::class, 'index'])->name('stok-menipis.index');
    });

    // ─── PENGADAAN & MANAJEMEN PESANAN ───
    Route::middleware(['role:Pemilik'])->group(function () {
        // Pengadaan
        Route::resource('pengadaan', \App\Http\Controllers\PengadaanController::class)->except(['edit', 'update', 'destroy']);
        Route::patch('/pengadaan/{pengadaan}/status', [\App\Http\Controllers\PengadaanController::class, 'updateStatus'])->name('pengadaan.update-status');
        Route::post('/pengadaan/{pengadaan}/realisasi', [\App\Http\Controllers\PengadaanController::class, 'realisasi'])->name('pengadaan.realisasi');
        
        // Pesanan Catering
        Route::get('/admin/pesanan/catering', [\App\Http\Controllers\PesananCateringController::class, 'index'])->name('admin.pesanan.catering.index');
        Route::get('/admin/pesanan/catering/{pesanan}', [\App\Http\Controllers\PesananCateringController::class, 'show'])->name('admin.pesanan.catering.show');
        Route::patch('/admin/pesanan/catering/{pesanan}/konfirmasi', [\App\Http\Controllers\PesananCateringController::class, 'konfirmasi'])->name('admin.pesanan.catering.konfirmasi');
        Route::patch('/admin/pesanan/catering/{pesanan}/update-status', [\App\Http\Controllers\PesananCateringController::class, 'updateStatus'])->name('admin.pesanan.catering.update-status');
        Route::patch('/admin/bukti/{buktiId}/verifikasi-dp', [\App\Http\Controllers\PesananCateringController::class, 'verifikasiDp'])->name('admin.bukti.verifikasi-dp');

        // Pesanan Nasi Box
        Route::get('/admin/pesanan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'index'])->name('admin.pesanan.nasibox.index');
        Route::get('/admin/pesanan/nasi-box/{pesanan}', [\App\Http\Controllers\PesananNasiBoxController::class, 'show'])->name('admin.pesanan.nasibox.show');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/konfirmasi', [\App\Http\Controllers\PesananNasiBoxController::class, 'konfirmasi'])->name('admin.pesanan.nasibox.konfirmasi');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/update-status', [\App\Http\Controllers\PesananNasiBoxController::class, 'updateStatus'])->name('admin.pesanan.nasibox.update-status');

        // Notifikasi Admin
        Route::get('/admin/notifikasi', function() {
            $notifikasis = \App\Models\NotifikasiAdmin::latest()->paginate(15);
            \App\Models\NotifikasiAdmin::where('is_read', false)->update(['is_read' => true]);
            return view('admin.notifikasi.index', compact('notifikasis'));
        })->name('admin.notifikasi.index');

        Route::post('/admin/notifikasi/read-all', function() {
            \App\Models\NotifikasiAdmin::where('is_read', false)->update(['is_read' => true]);
            return back()->with('success', 'Semua notifikasi telah ditandai dibaca.');
        })->name('admin.notifikasi.read-all');
    });

    // ─── JADWAL PENGANTARAN ───
    Route::middleware(['role:Tim Pengantaran'])->group(function () {
        Route::get('/admin/jadwal', [\App\Http\Controllers\JadwalPengantaranController::class, 'index'])->name('admin.jadwal.index');
        Route::patch('/admin/jadwal/{jenis}/{id}/status', [\App\Http\Controllers\JadwalPengantaranController::class, 'updateStatus'])->name('admin.jadwal.update-status');
    });

    // ─── LAPORAN ───
    Route::middleware(['role:Manajer,Pemilik'])->group(function () {
        Route::get('/laporan/stok', [\App\Http\Controllers\LaporanController::class, 'stok'])->name('laporan.stok');
        Route::get('/laporan/stok/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakStok'])->name('laporan.stok.cetak');
        Route::get('/laporan/penjualan', [\App\Http\Controllers\LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakPenjualan'])->name('laporan.penjualan.cetak');
        Route::get('/laporan/catering', [\App\Http\Controllers\LaporanController::class, 'catering'])->name('laporan.catering');
        Route::get('/laporan/catering/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakCatering'])->name('laporan.catering.cetak');
        Route::get('/laporan/nasi-box', [\App\Http\Controllers\LaporanController::class, 'nasibox'])->name('laporan.nasibox');
        Route::get('/laporan/nasi-box/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakNasiBox'])->name('laporan.nasibox.cetak');
    });

    // ─── KASIR & MANAJEMEN (DINE-IN & PESANAN) ───
    Route::middleware(['role:Kasir,Manajer,Pemilik'])->group(function () {
        // Dine-In Table Management
        Route::get('/pos/dinein', [\App\Http\Controllers\Pos\DineInController::class, 'index'])->name('pos.dinein.index');
        Route::post('/pos/dinein/store', [\App\Http\Controllers\Pos\DineInController::class, 'storePosOrder'])->name('pos.dinein.store-pos');
        Route::patch('/pos/dinein/meja/{meja}/clear', [\App\Http\Controllers\Pos\DineInController::class, 'clearTable'])->name('pos.dinein.clear-table');
        
        // Dine-In Checkout / Payment
        Route::get('/pos/dinein/meja/{meja}/checkout', [\App\Http\Controllers\Pos\DineInPaymentController::class, 'checkout'])->name('pos.dinein.checkout');
        Route::post('/pos/dinein/meja/{meja}/checkout', [\App\Http\Controllers\Pos\DineInPaymentController::class, 'processPayment'])->name('pos.dinein.processPayment');
        
        // Cetak Struk Dine In & QR Meja & Sub Status Update
        Route::get('/pos/dinein/qr-codes', [\App\Http\Controllers\Pos\DineInController::class, 'printQrMeja'])->name('pos.dinein.print-qr');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-dapur', [\App\Http\Controllers\Pos\DineInController::class, 'printDapur'])->name('pos.dinein.print-dapur');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-meja', [\App\Http\Controllers\Pos\DineInController::class, 'printMeja'])->name('pos.dinein.print-meja');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-gabungan', [\App\Http\Controllers\Pos\DineInController::class, 'printGabungan'])->name('pos.dinein.print-gabungan');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-nota', [\App\Http\Controllers\Pos\DineInController::class, 'printNota'])->name('pos.dinein.print-nota');
        Route::patch('/pos/dinein/pesanan/{pesananId}/sub-status', [\App\Http\Controllers\Pos\DineInController::class, 'updateSubStatus'])->name('pos.dinein.sub-status');
        Route::post('/pos/dinein/pesanan/{pesananId}/void', [\App\Http\Controllers\Pos\DineInController::class, 'voidOrder'])->name('pos.dinein.void');
        
        // Pesanan CRUD (Update Status)
        Route::resource('pesanan', \App\Http\Controllers\PesananController::class)->except(['create', 'edit', 'update', 'destroy']);
        Route::patch('/pesanan/{pesanan}/status', [\App\Http\Controllers\PesananController::class, 'updateStatus'])->name('pesanan.update-status');
        Route::get('/pesanan/{pesanan}/cetak/{type}', [\App\Http\Controllers\PesananController::class, 'cetak'])->name('pesanan.cetak');
    });

    // ─── MIDTRANS PAYMENT API ROUTES ───
    Route::post('/api/payment/qris', [\App\Http\Controllers\PaymentController::class, 'createQris']);
    Route::get('/api/payment/status/{orderId}', [\App\Http\Controllers\PaymentController::class, 'checkStatus']);
    Route::post('/api/payment/notification', [\App\Http\Controllers\PaymentController::class, 'handleNotification']);

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
Route::post('/api/midtrans/callback', [\App\Http\Controllers\MidtransController::class, 'notificationCallback']);

// Alternate Flow 6a: Fallback Polling / Manual Check Route
Route::get('/pesan/check-midtrans-status/{kodePesanan}', [\App\Http\Controllers\MidtransController::class, 'checkStatusManual'])->name('pesanan.check-midtrans-status');

// Fallback untuk Localhost (karena webhook Midtrans tidak bisa masuk ke localhost)
Route::post('/api/midtrans/localhost-fallback', function(\Illuminate\Http\Request $request) {
    $kode = $request->kode_pesanan;
    $order = null;
    if (strpos($kode, 'CTR') === 0) {
        $order = \App\Models\PesananCatering::where('kode_pesanan', $kode)->first();
    } else if (strpos($kode, 'NBX') === 0) {
        $order = \App\Models\PesananNasiBox::where('kode_pesanan', $kode)->first();
    } else if (strpos($kode, 'DIN') === 0) {
        $order = \App\Models\PesananDinein::with('items.menu')->where('kode_pesanan', $kode)->first();
    }

    if ($order) {
        if ($order instanceof \App\Models\PesananDinein) {
            $totalHarga = 0;
            foreach ($order->items as $item) {
                $totalHarga += ($item->qty * $item->menu->harga);
            }
            app(\App\Services\DineInService::class)->prosesPembayaran(
                $order->id,
                'qris',
                $totalHarga,
                $order->dibuka_oleh
            );
        } else {
            app(\App\Http\Controllers\MidtransController::class)->processSuccessPayment($order, $kode . ($order->status === 'terkonfirmasi' ? '-LNS-' : '-DP-'));
        }
    }

    return response()->json(['success' => true]);
})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Temporary routes for Email Preview Simulation
Route::get('/preview-email/invoice', function () {
    $pesanan = \App\Models\PesananCatering::first();
    if (!$pesanan) {
        $pesanan = new \App\Models\PesananCatering([
            'nama_pemesan' => 'Budi Santoso',
            'kode_pesanan' => 'CTR-DEMO-001',
            'tanggal_acara' => '2026-08-17',
            'total_tagihan' => 5000000,
            'dp_amount' => 1000000,
        ]);
    }
    return new \App\Mail\OrderInvoiceMail($pesanan);
});

Route::get('/preview-email/receipt', function () {
    $pesanan = \App\Models\PesananCatering::first();
    if (!$pesanan) {
        $pesanan = new \App\Models\PesananCatering([
            'nama_pemesan' => 'Budi Santoso',
            'kode_pesanan' => 'CTR-DEMO-001',
            'tanggal_acara' => '2026-08-17',
            'total_tagihan' => 5000000,
            'dp_amount' => 1000000,
        ]);
    }
    return new \App\Mail\PaymentReceiptMail($pesanan);
});
