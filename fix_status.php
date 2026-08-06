<?php
$files = [
    'app/Models/PembayaranDinein.php',
    'app/Http/Controllers/PesananCateringController.php',
    'app/Http/Controllers/DashboardController.php',
    'app/Http/Controllers/BuktiPembayaranController.php',
    'app/Http/Controllers/Pos/DineInController.php',
    'resources/views/order/catering/index.blade.php',
    'resources/views/order/catering/_detail.blade.php',
    'resources/views/order/catering/show.blade.php',
    'resources/views/order/nasi-box/index.blade.php',
    'resources/views/order/nasi-box/show.blade.php',
    'resources/views/akun/pesanan.blade.php',
    'resources/views/laporan/penjualan/index.blade.php',
    'resources/views/laporan/penjualan/detail.blade.php',
    'resources/views/pos/pembayaran/index.blade.php',
    'resources/views/pos/pembayaran/sukses.blade.php',
    'resources/views/pesanan/invoice-pdf.blade.php',
    'resources/views/lacak/index.blade.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        $content = str_replace("whereIn('status_pembayaran_id', [2, 3])", "where('status_verifikasi', 'diterima')", $content);
        $content = str_replace("where('status_pembayaran_id', 3)", "where('status_verifikasi', 'diterima')", $content);
        $content = str_replace("where('status_pembayaran_id', 1)", "where('status_verifikasi', 'menunggu_verifikasi')", $content);
        $content = str_replace("status_pembayaran_id == 3", "status_verifikasi === 'diterima'", $content);
        $content = str_replace("status_pembayaran_id === 3", "status_verifikasi === 'diterima'", $content);
        $content = str_replace("status_pembayaran_id === 1", "status_verifikasi === 'menunggu_verifikasi'", $content);
        $content = str_replace("firstWhere('status_pembayaran_id', 1)", "firstWhere('status_verifikasi', 'menunggu_verifikasi')", $content);
        
        file_put_contents($file, $content);
    }
}
echo "Done";
