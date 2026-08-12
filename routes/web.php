<?php

use App\Http\Controllers\Admin\MejaController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\BuktiPembayaranController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JadwalPengantaranController;
use App\Http\Controllers\KategoriMenuController;
use App\Http\Controllers\KetersediaanMenuController;
use App\Http\Controllers\LacakPesananController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MenuController;

use App\Http\Controllers\MutasiStokController;
use App\Http\Controllers\NotifikasiStokController;
use App\Http\Controllers\PaketCateringController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PemasokController;
use App\Http\Controllers\PenyesuaianStokController;
use App\Http\Controllers\PesananCateringController;
use App\Http\Controllers\PesananController;
use App\Http\Controllers\PesananDineInController;
use App\Http\Controllers\PesananNasiBoxController;
use App\Http\Controllers\Pos\DineInController;
use App\Http\Controllers\Pos\DineInPaymentController;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QrMenuController;
use App\Http\Controllers\ResepController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StokCateringController;
use App\Http\Controllers\StokMenipisController;
use App\Http\Controllers\StokOperasionalController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

// ─── PUBLIK — QR Self-Order (hanya via QR Code meja) ───────────────────────
Route::get('/qr-scanner', [QrMenuController::class, 'scanner'])->name('qr.scanner');
Route::get('/qr-menu/{token}', [QrMenuController::class, 'index'])->name('qr.menu');
Route::get('/qr-menu', [QrMenuController::class, 'index'])->name('qr.menu.no-token');
Route::post('/qr-menu/order', [QrMenuController::class, 'storeOrder'])->name('qr.menu.order');
Route::get('/qr-menu/{token}/status', [QrMenuController::class, 'statusChecker'])->name('qr.menu.status');
Route::get('/qr-menu/{token}/status/api', [QrMenuController::class, 'checkOrderStatus'])->name('qr.menu.status.api');

// ─── PESANAN (Katering & Nasi Box) ───────────────────────────────────────────────────
// Catering
Route::get('/pesan/catering', [PesananCateringController::class, 'create'])->name('pesan.catering');
Route::post('/pesan/catering', [PesananCateringController::class, 'store'])->name('pesan.catering.store');
Route::get('/pesan/catering/komponen/{paketId}', [PesananCateringController::class, 'getKomponen'])->name('pesan.catering.komponen');
Route::post('/pesan/catering/preview', [PesananCateringController::class, 'preview'])->name('pesan.catering.preview');

// Nasi Box
Route::get('/pesan/nasi-box', [PesananNasiBoxController::class, 'create'])->name('pesan.nasibox');
Route::post('/pesan/nasi-box', [PesananNasiBoxController::class, 'store'])->name('pesan.nasibox.store');
Route::post('/pesan/nasi-box/preview', [PesananNasiBoxController::class, 'preview'])->name('pesan.nasibox.preview');

// ─── PUBLIK — Bayar & Status ─────────────────────────────────────────────────
Route::get('/pesan/bayar', [BuktiPembayaranController::class, 'cari'])->name('pesanan.bayar.cari');
Route::get('/pesan/bayar/status/{kodePesanan}', [BuktiPembayaranController::class, 'statusJson'])->name('pesanan.bayar.status');
Route::get('/pesan/bayar/{kodePesanan}', [BuktiPembayaranController::class, 'show'])->name('pesanan.bayar');
Route::post('/pesan/bukti', [BuktiPembayaranController::class, 'store'])->name('pesanan.bukti.store');



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
        Route::resource('users', UserController::class)->except(['create', 'edit', 'update']);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('pelanggan/{pelanggan}', [UserController::class, 'showPelanggan'])->name('pelanggan.show');
        Route::delete('pelanggan/{pelanggan}', [UserController::class, 'destroyPelanggan'])->name('pelanggan.destroy');
        Route::resource('roles', RoleController::class)->except(['create', 'show', 'edit']);
    });

    // ─── MASTER DATA (Menu, Bahan Baku, Paket, Meja) ───
    Route::middleware(['role:Admin,Manajer,Pemilik'])->group(function () {
        // Pengadaan Bahan
        Route::get('pengadaan/permintaan', [\App\Http\Controllers\PengadaanController::class, 'index'])->name('pengadaan.permintaan.index');
        Route::get('pengadaan/harian/create', [\App\Http\Controllers\PengadaanController::class, 'createHarian'])->name('pengadaan.harian.create');
        Route::post('pengadaan/harian', [\App\Http\Controllers\PengadaanController::class, 'storeHarian'])->name('pengadaan.harian.store');
        Route::get('pengadaan/catering/create', [\App\Http\Controllers\PengadaanController::class, 'createCatering'])->name('pengadaan.catering.create');
        Route::post('pengadaan/catering', [\App\Http\Controllers\PengadaanController::class, 'storeCatering'])->name('pengadaan.catering.store');
        Route::post('pengadaan/permintaan/{pengadaan}/batal', [\App\Http\Controllers\PengadaanController::class, 'cancel'])->name('pengadaan.permintaan.cancel');
        Route::get('pengadaan/permintaan/{pengadaan}', [\App\Http\Controllers\PengadaanController::class, 'show'])->name('pengadaan.permintaan.show');
        
        Route::get('pengadaan/po', [\App\Http\Controllers\PurchaseOrderController::class, 'index'])->name('pengadaan.po.index');
        Route::get('pengadaan/po/{po}/print', [\App\Http\Controllers\PurchaseOrderController::class, 'print'])->name('pengadaan.po.print');
        Route::post('pengadaan/po/{po}/batal', [\App\Http\Controllers\PurchaseOrderController::class, 'cancel'])->name('pengadaan.po.cancel');
        Route::get('pengadaan/po/{po}', [\App\Http\Controllers\PurchaseOrderController::class, 'show'])->name('pengadaan.po.show');

        Route::get('pengadaan/permintaan/{pengadaan}/po/create', [\App\Http\Controllers\PurchaseOrderController::class, 'create'])->name('pengadaan.po.create');
        Route::post('pengadaan/permintaan/{pengadaan}/po', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('pengadaan.po.store');
        Route::post('pengadaan/permintaan/{pengadaan}/po/baru', [\App\Http\Controllers\PurchaseOrderController::class, 'store'])->name('pengadaan.po.store-baru');

        Route::get('pengadaan/penerimaan', [\App\Http\Controllers\PenerimaanBahanController::class, 'index'])->name('pengadaan.penerimaan.index');
        Route::get('pengadaan/penerimaan/po/{po}/create', [\App\Http\Controllers\PenerimaanBahanController::class, 'create'])->name('pengadaan.penerimaan.create');
        Route::get('pengadaan/penerimaan/{penerimaan}', [\App\Http\Controllers\PenerimaanBahanController::class, 'show'])->name('pengadaan.penerimaan.show');
        Route::post('pengadaan/penerimaan/po/{po}', [\App\Http\Controllers\PenerimaanBahanController::class, 'store'])->name('pengadaan.penerimaan.store');
        Route::get('bahan-baku/{id}/drawer', [BahanBakuController::class, 'drawer'])->name('bahan-baku.drawer');
        Route::resource('bahan-baku', BahanBakuController::class);
        
        Route::post('kategori-bahan', [BahanBakuController::class, 'storeKategori'])->name('kategori-bahan.store');
        Route::put('kategori-bahan/{id}', [BahanBakuController::class, 'updateKategori'])->name('kategori-bahan.update');
        Route::delete('kategori-bahan/{id}', [BahanBakuController::class, 'destroyKategori'])->name('kategori-bahan.destroy');
        
        Route::post('satuan', [BahanBakuController::class, 'storeSatuan'])->name('satuan.store');
        Route::put('satuan/{id}', [BahanBakuController::class, 'updateSatuan'])->name('satuan.update');
        Route::delete('satuan/{id}', [BahanBakuController::class, 'destroySatuan'])->name('satuan.destroy');
        
        Route::post('satuan/ajax', [BahanBakuController::class, 'storeSatuanAjax'])->name('satuan.ajax.store');
        Route::resource('kategori-menu', KategoriMenuController::class)->except(['create', 'show', 'edit']);
        Route::patch('/kategori-menu/{kategori_menu}/toggle', [KategoriMenuController::class, 'toggleStatus'])->name('kategori-menu.toggle');
        Route::resource('menu', MenuController::class);
        Route::patch('/menu/{menu}/toggle', [MenuController::class, 'toggleStatus'])->name('menu.toggle');
        Route::get('/resep', [ResepController::class, 'index'])->name('resep.index');
        Route::get('/menu/{menu}/resep/create', [ResepController::class, 'create'])->name('resep.create');
        Route::post('/menu/{menu}/resep', [ResepController::class, 'store'])->name('resep.store');
        Route::delete('/menu/{menu}/resep', [ResepController::class, 'destroy'])->name('resep.destroy');
        Route::post('/menu/{menu}/komposisi', [ResepController::class, 'storeKomposisi'])->name('resep.komposisi.store');

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
        Route::get('/ketersediaan-menu', [KetersediaanMenuController::class, 'index'])->name('ketersediaan-menu.index');
        // Notifikasi Stok
        Route::get('/notifikasi-stok', [NotifikasiStokController::class, 'index'])->name('notifikasi-stok.index');
        Route::post('/notifikasi-stok/{id}/read', [NotifikasiStokController::class, 'markAsRead'])->name('notifikasi-stok.read');
        Route::post('/notifikasi-stok/mark-all-read', [NotifikasiStokController::class, 'markAllAsRead'])->name('notifikasi-stok.mark-all-read');
        Route::post('/notifikasi-stok/check-now', [NotifikasiStokController::class, 'checkNow'])->name('notifikasi-stok.check-now');
        Route::get('/notifikasi-stok/unread-count', [NotifikasiStokController::class, 'getUnreadCount'])->name('notifikasi-stok.unread-count');
        // Stok Menipis
        Route::get('/stok-menipis', [StokMenipisController::class, 'index'])->name('stok-menipis.index');
        // Penyesuaian Stok
        Route::get('/penyesuaian-stok', [PenyesuaianStokController::class, 'index'])->name('penyesuaian-stok.index');
        Route::get('/penyesuaian-stok/create', [PenyesuaianStokController::class, 'create'])->name('penyesuaian-stok.create');
        Route::post('/penyesuaian-stok', [PenyesuaianStokController::class, 'store'])->name('penyesuaian-stok.store');
        Route::get('/penyesuaian-stok/{id}', [PenyesuaianStokController::class, 'show'])->name('penyesuaian-stok.show');
    });

    // ─── ADMIN ROUTES (Membutuhkan middleware auth, dll) ───
    Route::middleware(['auth'])->group(function () {

        // ── PESANAN (SEMUA TRANSAKSI) ──
        Route::get('/admin/pesanan', [App\Http\Controllers\Admin\PesananController::class, 'index'])->name('admin.pesanan.index');
        
        // Pembayaran
        Route::get('/admin/pembayaran', [App\Http\Controllers\Admin\PembayaranController::class, 'index'])->name('admin.pembayaran.index');
        Route::get('/admin/pembayaran/detail/{id}', [App\Http\Controllers\Admin\PembayaranController::class, 'show'])->name('admin.pembayaran.show');
        Route::post('/admin/pembayaran/{id}/verify', [App\Http\Controllers\Admin\PembayaranController::class, 'verify'])->name('admin.pembayaran.verify');
        Route::post('/admin/pembayaran/{id}/cancel', [App\Http\Controllers\Admin\PembayaranController::class, 'cancel'])->name('admin.pembayaran.cancel');
        Route::get('/admin/pesanan/detail/{id}', [App\Http\Controllers\Admin\PesananController::class, 'show'])->name('admin.pesanan.show');

    });

    // ─── PESANAN DINE IN (Kasir & Pemilik) ───
    Route::middleware(['role:Admin,Pemilik,Kasir'])->group(function () {
        Route::get('/admin/pesanan/dinein', [PesananDineInController::class, 'index'])->name('admin.pesanan.dinein.index');
    });

    // ─── PESANAN CATERING & NASI BOX (Pemilik & Admin) ───
    Route::middleware(['role:Admin,Pemilik,Pengantaran'])->group(function () {
        Route::get('/admin/pesanan/catering/{pesanan}', [PesananCateringController::class, 'show'])->name('admin.pesanan.catering.show');
        Route::get('/admin/pesanan/nasi-box/{pesanan}', [PesananNasiBoxController::class, 'show'])->name('admin.pesanan.nasibox.show');
    });

    Route::middleware(['role:Admin,Pemilik'])->group(function () {
        // Pesanan Catering
        Route::get('/admin/pesanan/catering', [PesananCateringController::class, 'index'])->name('admin.pesanan.catering.index');
        Route::get('/admin/pesanan/catering/{pesanan}/pdf', [PesananCateringController::class, 'exportPdf'])->name('admin.pesanan.catering.pdf');
        Route::patch('/admin/pesanan/catering/{pesanan}/konfirmasi', [PesananCateringController::class, 'konfirmasi'])->name('admin.pesanan.catering.konfirmasi');
        Route::patch('/admin/pesanan/catering/{pesanan}/update-status', [PesananCateringController::class, 'updateStatus'])->name('admin.pesanan.catering.update-status');
        Route::patch('/admin/bukti/{buktiId}/verifikasi-dp', [PesananCateringController::class, 'verifikasiDp'])->name('admin.bukti.verifikasi-dp');

        // Pesanan Nasi Box
        Route::get('/admin/pesanan/nasi-box', [PesananNasiBoxController::class, 'index'])->name('admin.pesanan.nasibox.index');
        Route::get('/admin/pesanan/nasi-box/{pesanan}/pdf', [PesananNasiBoxController::class, 'exportPdf'])->name('admin.pesanan.nasibox.pdf');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/konfirmasi', [PesananNasiBoxController::class, 'konfirmasi'])->name('admin.pesanan.nasibox.konfirmasi');
        Route::patch('/admin/pesanan/nasi-box/{pesanan}/update-status', [PesananNasiBoxController::class, 'updateStatus'])->name('admin.pesanan.nasibox.update-status');

        // Jadwal Pengantaran
        Route::get('/admin/jadwal-pengantaran', [JadwalPengantaranController::class, 'index'])->name('admin.jadwal-pengantaran.index');
        Route::patch('/admin/jadwal-pengantaran/{id}/update-status', [JadwalPengantaranController::class, 'updateStatus'])->name('admin.jadwal-pengantaran.update-status');
        Route::patch('/admin/jadwal-pengantaran/{id}/pengantaran-status', [JadwalPengantaranController::class, 'updatePengantaranStatus'])->name('admin.jadwal-pengantaran.update-pengantaran-status');
        Route::get('/admin/jadwal-pengantaran/pdf', [JadwalPengantaranController::class, 'exportPdf'])->name('admin.jadwal-pengantaran.pdf');
    });

    // ─── LACAK PESANAN (Bisa untuk tamu/guest) ───
    Route::get('/lacak-pesanan', [App\Http\Controllers\LacakPesananController::class, 'index'])->name('lacak.index');
    
    // ─── API LOKASI & JARAK ───
    Route::middleware(['role:Admin,Pengantaran'])->group(function () {
        Route::get('/admin/jadwal', [JadwalPengantaranController::class, 'index'])->name('admin.jadwal.index');
        Route::patch('/admin/jadwal/{jenis}/{id}/status', [JadwalPengantaranController::class, 'updateStatus'])->name('admin.jadwal.update-status');
        Route::patch('/admin/jadwal/{id}/pengantaran-status', [JadwalPengantaranController::class, 'updatePengantaranStatus'])->name('admin.jadwal.update-pengantaran-status');
        Route::post('/admin/jadwal/{id}/assign-kurir', [JadwalPengantaranController::class, 'assignKurir'])->name('admin.jadwal.assign-kurir');
    });

    // ─── LAPORAN ───
    Route::middleware(['role:Admin,Pemilik,Manajer'])->group(function () {
        // Laporan Penjualan
        Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
        Route::get('/laporan/penjualan/cetak-pdf', [LaporanController::class, 'cetakPenjualanPdf'])->name('laporan.penjualan.cetak-pdf');
        Route::get('/laporan/penjualan/cetak-excel', [LaporanController::class, 'cetakPenjualanExcel'])->name('laporan.penjualan.cetak-excel');
        Route::get('/laporan/penjualan/detail/{id}', [LaporanController::class, 'detailPenjualan'])->name('laporan.penjualan.detail');

        // Laporan Persediaan
        Route::get('/laporan/persediaan', [LaporanController::class, 'persediaan'])->name('laporan.persediaan');
        Route::get('/laporan/persediaan/cetak-pdf', [LaporanController::class, 'cetakPersediaanPdf'])->name('laporan.persediaan.cetak-pdf');
        Route::get('/laporan/persediaan/cetak-excel', [LaporanController::class, 'cetakPersediaanExcel'])->name('laporan.persediaan.cetak-excel');
        Route::get('/laporan/persediaan/detail/{id}', [LaporanController::class, 'detailPersediaan'])->name('laporan.persediaan.detail');

        // Laporan Pengadaan
        Route::get('/laporan/pengadaan', [LaporanController::class, 'pengadaan'])->name('laporan.pengadaan');
        Route::get('/laporan/pengadaan/cetak-pdf', [LaporanController::class, 'cetakPengadaanPdf'])->name('laporan.pengadaan.cetak-pdf');
        Route::get('/laporan/pengadaan/cetak-excel', [LaporanController::class, 'cetakPengadaanExcel'])->name('laporan.pengadaan.cetak-excel');
        Route::get('/laporan/pengadaan/detail/{id}', [LaporanController::class, 'detailPengadaan'])->name('laporan.pengadaan.detail');
    });

    // ─── KASIR & MANAJEMEN (DINE-IN & PESANAN) ───
    // Route yang bisa diakses Kasir, Pemilik, dan Manajer
    Route::middleware(['role:Admin,Kasir,Pemilik,Manajer'])->group(function () {
        Route::get('/pos/dinein/qr-codes', [DineInController::class, 'printQrMeja'])->name('pos.dinein.print-qr');
    });

    Route::middleware(['role:Admin,Kasir,Pemilik,Manajer'])->group(function () {
        // Dine-In Table Management (view + table status + checker meja & penyajian)
        Route::get('/pos/dinein', [DineInController::class, 'index'])->name('pos.dinein.index');
        Route::get('/pos/dinein/table-status', [DineInController::class, 'tableStatusApi'])->name('pos.dinein.table-status');
        Route::post('/pos/dinein/pesanan/{pesanan}/konfirmasi', [DineInController::class, 'konfirmasi'])->name('pos.dinein.konfirmasi');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-meja', [DineInController::class, 'printMeja'])->name('pos.dinein.print-meja');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-gabungan', [DineInController::class, 'printGabungan'])->name('pos.dinein.print-gabungan');
        Route::patch('/pos/dinein/item/{itemId}/toggle-sajian', [DineInController::class, 'toggleStatusSajian'])->name('pos.dinein.toggle-sajian');
    });

    Route::middleware(['role:Admin,Kasir,Pemilik,Manajer'])->group(function () {
        // Dine-In Table Management (kasir)
        Route::post('/pos/dinein/store', [DineInController::class, 'storePosOrder'])->name('pos.dinein.store-pos');
        Route::patch('/pos/dinein/meja/{meja}/clear', [DineInController::class, 'clearTable'])->name('pos.dinein.clear-table');

        // Dine-In Checkout / Payment
        Route::get('/pos/dinein/meja/{meja}/checkout', [DineInPaymentController::class, 'checkout'])->name('pos.dinein.checkout');
        Route::post('/pos/dinein/meja/{meja}/checkout', [DineInPaymentController::class, 'processPayment'])->name('pos.dinein.processPayment');
        Route::post('/pos/dinein/pesanan/{pesanan}/charge-qris', [DineInPaymentController::class, 'chargeQris'])->name('pos.dinein.charge_qris');
        Route::get('/pos/dinein/pembayaran/{pembayaran}/qris', [DineInPaymentController::class, 'showQris'])->name('pos.dinein.show_qris');
        Route::get('/pos/dinein/pembayaran/{pembayaran}/status', [DineInPaymentController::class, 'checkStatus'])->name('pos.dinein.check_status');
        Route::get('/pos/dinein/success/{pesananId}', [DineInPaymentController::class, 'success'])->name('pos.dinein.success');

        // Cetak Struk Dine In & Sub Status Update
        Route::get('/pos/dinein/pesanan/{pesananId}/print-dapur', [DineInController::class, 'printDapur'])->name('pos.dinein.print-dapur');
        Route::get('/pos/dinein/pesanan/{pesananId}/print-nota', [DineInController::class, 'printNota'])->name('pos.dinein.print-nota');
        Route::patch('/pos/dinein/pesanan/{pesananId}/sub-status', [DineInController::class, 'updateSubStatus'])->name('pos.dinein.sub-status');
        Route::patch('/pos/dinein/kot/{kotId}/status', [DineInController::class, 'updateKotStatus'])->name('pos.dinein.kot-status');
        Route::post('/pos/dinein/pesanan/{pesananId}/void', [DineInController::class, 'voidOrder'])->name('pos.dinein.void');

        // Pesanan CRUD (Update Status)
        Route::resource('pesanan', PesananController::class)->except(['create', 'edit', 'update', 'destroy']);
        Route::patch('/pesanan/{pesanan}/status', [PesananController::class, 'updateStatus'])->name('pesanan.update-status');
        Route::get('/pesanan/{pesanan}/cetak/{type}', [PesananController::class, 'cetak'])->name('pesanan.cetak');
        
        // Verifikasi Pembayaran
        Route::prefix('verifikasi-pembayaran')->name('admin.verifikasi_pembayaran.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\VerifikasiPembayaranController::class, 'index'])->name('index');
            Route::post('/{id}/process', [\App\Http\Controllers\Admin\VerifikasiPembayaranController::class, 'process'])->name('process');
        });
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

// Routes for Pemasok
Route::middleware(['auth', 'role:Super Admin,Manajer,Pemilik'])->group(function () {
    Route::resource('inventory/pemasok', PemasokController::class);
});

