<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\LandingController::class, 'index'])->name('home');

// ─── PUBLIK — Form Catering ───────────────────────────────────────────────────
Route::get('/pesan/catering', [\App\Http\Controllers\PesananCateringController::class, 'create'])->name('pesan.catering');
Route::post('/pesan/catering', [\App\Http\Controllers\PesananCateringController::class, 'store'])->name('pesan.catering.store');
Route::get('/pesan/catering/komponen/{paketId}', [\App\Http\Controllers\PesananCateringController::class, 'getKomponen'])->name('pesan.catering.komponen');
Route::post('/pesan/catering/preview', [\App\Http\Controllers\PesananCateringController::class, 'preview'])->name('pesan.catering.preview');

// ─── PUBLIK — QR Self-Order (hanya via QR Code meja) ───────────────────────
Route::get('/qr-scanner', [\App\Http\Controllers\QrMenuController::class, 'scanner'])->name('qr.scanner');
Route::get('/qr-menu/{token}', [\App\Http\Controllers\QrMenuController::class, 'index'])->name('qr.menu');
Route::get('/qr-menu', [\App\Http\Controllers\QrMenuController::class, 'index'])->name('qr.menu.no-token');
Route::post('/qr-menu/order', [\App\Http\Controllers\QrMenuController::class, 'storeOrder'])->name('qr.menu.order');

// ─── PUBLIK — Form Nasi Box ───────────────────────────────────────────────────
Route::get('/pesan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'create'])->name('pesan.nasibox');
Route::post('/pesan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'store'])->name('pesan.nasibox.store');
Route::post('/pesan/nasi-box/preview', [\App\Http\Controllers\PesananNasiBoxController::class, 'preview'])->name('pesan.nasibox.preview');

// ─── PUBLIK — Bayar & Status ─────────────────────────────────────────────────
Route::get('/pesan/bayar/{kodePesanan}', [\App\Http\Controllers\BuktiPembayaranController::class, 'show'])->name('pesanan.bayar');
Route::post('/pesan/bukti', [\App\Http\Controllers\BuktiPembayaranController::class, 'store'])->name('pesanan.bukti.store');

Route::get('/lacak-pesanan', [\App\Http\Controllers\LacakPesananController::class, 'index'])->name('lacak.index');
Route::get('/pesan/invoice/{kodePesanan}', [\App\Http\Controllers\BuktiPembayaranController::class, 'invoicePdf'])->name('pesanan.invoice');

// Legacy redirect agar link lama tidak broken
Route::get('/pesan-catering', function() { return redirect()->route('pesan.catering'); });

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/pos/menu/{menu}/toggle-status', [\App\Http\Controllers\Pos\DineInController::class, 'toggleMenuStatus'])->name('pos.menu.toggle-status');
    


    // Route Khusus Admin
    Route::middleware(['role:Admin'])->group(function () {
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['create', 'show', 'edit']);
        Route::resource('roles', \App\Http\Controllers\RoleController::class)->except(['create', 'show', 'edit']);
    });

    // ─── MASTER DATA (Menu, Bahan Baku, Paket, Meja) ───
    Route::middleware(['role:Admin,Manajer,Pemilik'])->group(function () {
        Route::resource('bahan-baku', \App\Http\Controllers\BahanBakuController::class);
        Route::resource('kategori-menu', \App\Http\Controllers\KategoriMenuController::class)->except(['create', 'show', 'edit']);
        Route::resource('menu', \App\Http\Controllers\MenuController::class);
        Route::patch('/menu/{menu}/toggle', [\App\Http\Controllers\MenuController::class, 'toggleStatus'])->name('menu.toggle');
        Route::get('/resep', [\App\Http\Controllers\ResepController::class, 'index'])->name('resep.index');
        Route::get('/menu/{menu}/resep/create', [\App\Http\Controllers\ResepController::class, 'create'])->name('resep.create');
        Route::post('/menu/{menu}/resep', [\App\Http\Controllers\ResepController::class, 'store'])->name('resep.store');
        
        Route::resource('paket-catering', \App\Http\Controllers\PaketCateringController::class);
        Route::patch('/paket-catering/{paketCatering}/toggle', [\App\Http\Controllers\PaketCateringController::class, 'toggleActive'])->name('paket-catering.toggle');
        
        Route::post('meja/{meja}/generate-qr', [\App\Http\Controllers\Admin\MejaController::class, 'generateQr'])->name('meja.generate-qr');
        Route::resource('meja', \App\Http\Controllers\Admin\MejaController::class)->except(['create', 'show', 'edit']);
    });

    // ─── STOK BAHAN BAKU & RIWAYAT (View only, semua tim internal) ───
    Route::middleware(['role:Admin,Manajer,Pemilik'])->group(function () {
        Route::get('/mutasi-stok', [\App\Http\Controllers\MutasiStokController::class, 'index'])->name('mutasi-stok.index');
        Route::get('/stok-operasional', [\App\Http\Controllers\StokOperasionalController::class, 'index'])->name('stok-operasional.index');
        Route::get('/stok-catering', [\App\Http\Controllers\StokCateringController::class, 'index'])->name('stok-catering.index');
    });

    // ─── PENGADAAN (Pemilik & Manajer) ───
    Route::middleware(['role:Admin,Pemilik,Manajer'])->group(function () {
        // Route spesifik HARUS didaftarkan sebelum resource agar tidak bentrok dengan wildcard {pengadaan}
        Route::get('/pengadaan/terima-barang', [\App\Http\Controllers\PengadaanController::class, 'terimaBarang'])->name('pengadaan.terima-barang');
        Route::get('/pengadaan/{pengadaan}/pdf', [\App\Http\Controllers\PengadaanController::class, 'exportPdf'])->name('pengadaan.pdf');
        Route::get('/pengadaan/{pengadaan}/terima', [\App\Http\Controllers\PengadaanController::class, 'formTerima'])->name('pengadaan.form-terima');
        Route::post('/pengadaan/{pengadaan}/terima', [\App\Http\Controllers\PengadaanController::class, 'prosesTerima'])->name('pengadaan.proses-terima');
        Route::resource('pengadaan', \App\Http\Controllers\PengadaanController::class)->except(['edit', 'update', 'destroy']);
    });

    // ─── ADMIN ROUTES (Membutuhkan middleware auth, dll) ───
    Route::middleware(['auth'])->group(function () {
        
        // ── PESANAN (SEMUA TRANSAKSI) ──
        Route::get('/admin/pesanan', [\App\Http\Controllers\Admin\PesananController::class, 'index'])->name('admin.pesanan.index');
        Route::get('/admin/pesanan/detail/{id}', [\App\Http\Controllers\Admin\PesananController::class, 'show'])->name('admin.pesanan.show');

    });

    // ─── PESANAN CATERING & NASI BOX (Pemilik) ───
    Route::middleware(['role:Admin,Pemilik'])->group(function () {
        // Pesanan Catering
        Route::get('/admin/pesanan/catering', [\App\Http\Controllers\PesananCateringController::class, 'index'])->name('admin.pesanan.catering.index');
        Route::get('/admin/pesanan/catering/{pesanan}', [\App\Http\Controllers\PesananCateringController::class, 'show'])->name('admin.pesanan.catering.show');
        Route::get('/admin/pesanan/catering/{pesanan}/pdf', [\App\Http\Controllers\PesananCateringController::class, 'exportPdf'])->name('admin.pesanan.catering.pdf');
        Route::patch('/admin/pesanan/catering/{pesanan}/konfirmasi', [\App\Http\Controllers\PesananCateringController::class, 'konfirmasi'])->name('admin.pesanan.catering.konfirmasi');
        Route::patch('/admin/pesanan/catering/{pesanan}/update-status', [\App\Http\Controllers\PesananCateringController::class, 'updateStatus'])->name('admin.pesanan.catering.update-status');
        Route::patch('/admin/bukti/{buktiId}/verifikasi-dp', [\App\Http\Controllers\PesananCateringController::class, 'verifikasiDp'])->name('admin.bukti.verifikasi-dp');

        // Pesanan Nasi Box
        Route::get('/admin/pesanan/nasi-box', [\App\Http\Controllers\PesananNasiBoxController::class, 'index'])->name('admin.pesanan.nasibox.index');
        Route::get('/admin/pesanan/nasi-box/{pesanan}', [\App\Http\Controllers\PesananNasiBoxController::class, 'show'])->name('admin.pesanan.nasibox.show');
        Route::get('/admin/pesanan/nasi-box/{pesanan}/pdf', [\App\Http\Controllers\PesananNasiBoxController::class, 'exportPdf'])->name('admin.pesanan.nasibox.pdf');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/konfirmasi', [\App\Http\Controllers\PesananNasiBoxController::class, 'konfirmasi'])->name('admin.pesanan.nasibox.konfirmasi');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/update-status', [\App\Http\Controllers\PesananNasiBoxController::class, 'updateStatus'])->name('admin.pesanan.nasibox.update-status');
        
        // Jadwal Pengantaran
        Route::get('/admin/jadwal-pengantaran', [\App\Http\Controllers\JadwalPengantaranController::class, 'index'])->name('admin.jadwal-pengantaran.index');
        Route::patch('/admin/jadwal-pengantaran/{id}/update-status', [\App\Http\Controllers\JadwalPengantaranController::class, 'updateStatus'])->name('admin.jadwal-pengantaran.update-status');
        Route::get('/admin/jadwal-pengantaran/pdf', [\App\Http\Controllers\JadwalPengantaranController::class, 'exportPdf'])->name('admin.jadwal-pengantaran.pdf');
    });

    // ─── JADWAL PENGANTARAN ───
    Route::middleware(['role:Admin,Tim Pengantaran'])->group(function () {
        Route::get('/admin/jadwal', [\App\Http\Controllers\JadwalPengantaranController::class, 'index'])->name('admin.jadwal.index');
        Route::patch('/admin/jadwal/{jenis}/{id}/status', [\App\Http\Controllers\JadwalPengantaranController::class, 'updateStatus'])->name('admin.jadwal.update-status');
    });

    // ─── LAPORAN ───
    Route::middleware(['role:Admin,Pemilik,Manajer'])->group(function () {
        Route::get('/laporan/stok', [\App\Http\Controllers\LaporanController::class, 'stok'])->name('laporan.stok');
        Route::get('/laporan/stok/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakStok'])->name('laporan.stok.cetak');
        Route::get('/laporan/penjualan', [\App\Http\Controllers\LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakPenjualan'])->name('laporan.penjualan.cetak');
        Route::get('/laporan/pengadaan', [\App\Http\Controllers\LaporanController::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/pengadaan/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakPengadaan'])->name('laporan.pengadaan.cetak');
        Route::get('/laporan/menu-terlaris', [\App\Http\Controllers\LaporanController::class, 'menuTerlaris'])->name('laporan.menu-terlaris');
        Route::get('/laporan/menu-terlaris/cetak', [\App\Http\Controllers\LaporanController::class, 'cetakMenuTerlaris'])->name('laporan.menu-terlaris.cetak');
    });

    // ─── KASIR & MANAJEMEN (DINE-IN & PESANAN) ───
    // Route yang bisa diakses Kasir, Pemilik, dan Manajer
    Route::middleware(['role:Admin,Kasir,Pemilik,Manajer'])->group(function () {
        Route::get('/pos/dinein/qr-codes', [\App\Http\Controllers\Pos\DineInController::class, 'printQrMeja'])->name('pos.dinein.print-qr');
    });

    Route::middleware(['role:Admin,Kasir,Pemilik,Manajer'])->group(function () {
        // Dine-In Table Management
        Route::get('/pos/dinein', [\App\Http\Controllers\Pos\DineInController::class, 'index'])->name('pos.dinein.index');
        Route::post('/pos/dinein/store', [\App\Http\Controllers\Pos\DineInController::class, 'storePosOrder'])->name('pos.dinein.store-pos');
        Route::patch('/pos/dinein/meja/{meja}/clear', [\App\Http\Controllers\Pos\DineInController::class, 'clearTable'])->name('pos.dinein.clear-table');
        
        // Dine-In Checkout / Payment
        Route::get('/pos/dinein/meja/{meja}/checkout', [\App\Http\Controllers\Pos\DineInPaymentController::class, 'checkout'])->name('pos.dinein.checkout');
        Route::post('/pos/dinein/meja/{meja}/checkout', [\App\Http\Controllers\Pos\DineInPaymentController::class, 'processPayment'])->name('pos.dinein.processPayment');
        Route::get('/pos/dinein/success/{pesananId}', [\App\Http\Controllers\Pos\DineInPaymentController::class, 'success'])->name('pos.dinein.success');
        
        // Cetak Struk Dine In & Sub Status Update
        Route::get('/pos/dinein/pesanan/{pesananId}/print-dapur', [\App\Http\Controllers\Pos\DineInController::class, 'printDapur'])->name('pos.dinein.print-dapur');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-meja', [\App\Http\Controllers\Pos\DineInController::class, 'printMeja'])->name('pos.dinein.print-meja');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-gabungan', [\App\Http\Controllers\Pos\DineInController::class, 'printGabungan'])->name('pos.dinein.print-gabungan');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-nota', [\App\Http\Controllers\Pos\DineInController::class, 'printNota'])->name('pos.dinein.print-nota');
        Route::patch('/pos/dinein/pesanan/{pesananId}/sub-status', [\App\Http\Controllers\Pos\DineInController::class, 'updateSubStatus'])->name('pos.dinein.sub-status');
        Route::patch('/pos/dinein/item/{itemId}/toggle-sajian', [\App\Http\Controllers\Pos\DineInController::class, 'toggleStatusSajian'])->name('pos.dinein.toggle-sajian');
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
