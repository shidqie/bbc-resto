<?php
$files = [
    'resources/views/pesanan/invoice-pdf.blade.php',
    'resources/views/lacak/index.blade.php',
    'resources/views/admin/pesanan/show_partial.blade.php',
    'resources/views/pos/pesanan/show.blade.php',
    'resources/views/admin/pesanan/show.blade.php',
    'resources/views/pos/pesanan/print-nota.blade.php',
    'resources/views/pos/pesanan/success.blade.php',
    'resources/views/pos/pesanan/print/konsumen.blade.php',
    'resources/views/admin/pembayaran/show.blade.php',
    'resources/views/admin/pembayaran/index.blade.php',
    'resources/views/laporan/penjualan/detail.blade.php',
    'resources/views/order/nasi-box/show.blade.php',
    'resources/views/order/catering/show.blade.php',
    'resources/views/order/catering/_detail.blade.php',
    'app/Http/Controllers/BuktiPembayaranController.php',
    'app/Http/Controllers/Admin/PesananController.php',
    'app/Http/Controllers/Admin/PembayaranController.php',
    'app/Http/Controllers/LaporanController.php',
    'app/Http/Controllers/PesananCateringController.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Fix eager loading
        $content = str_replace("'pembayaran.metode_pembayaran', ", "", $content);
        $content = str_replace(", 'pembayaran.metode_pembayaran'", "", $content);
        $content = str_replace("'pembayaran.metode_pembayaran'", "", $content);
        
        // Fix object access
        $content = str_replace("->metode_pembayaran->nama_metode", "->metode_pembayaran", $content);
        
        file_put_contents($file, $content);
    }
}
echo "Fixed metode_pembayaran references.\n";
