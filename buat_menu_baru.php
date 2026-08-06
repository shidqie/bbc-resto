<?php
require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

// Setup database connection
$capsule = new DB;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'db_bbc_resto',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
    'socket'    => '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock',
]);

$capsule->setEventDispatcher(new Dispatcher(new Container));
$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "=== MEMBUAT MENU BARU UNTUK BBC RESTO ===\n\n";

// Daftar menu baru yang akan dibuat
$menuBaru = [
    // Menu Dine In (jenis_menu_id = 1)
    ['nama_menu' => 'Nasi Liwet Komplit', 'kode_menu' => 'NL-001', 'harga_jual' => 35000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    ['nama_menu' => 'Ayam Goreng Kalasan', 'kode_menu' => 'AY-001', 'harga_jual' => 28000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    ['nama_menu' => 'Gurame Bakar Sambal Dabu', 'kode_menu' => 'IK-001', 'harga_jual' => 45000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    ['nama_menu' => 'Bebek Goreng Kremes', 'kode_menu' => 'BE-001', 'harga_jual' => 38000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    ['nama_menu' => 'Sate Ayam (10 tusuk)', 'kode_menu' => 'ST-001', 'harga_jual' => 25000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    ['nama_menu' => 'Sup Buntut Sapi', 'kode_menu' => 'SP-001', 'harga_jual' => 42000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    ['nama_menu' => 'Es Teh Manis', 'kode_menu' => 'MN-001', 'harga_jual' => 8000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    ['nama_menu' => 'Kopi Susu Gula Aren', 'kode_menu' => 'MN-002', 'harga_jual' => 15000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    ['nama_menu' => 'Jus Alpukat', 'kode_menu' => 'MN-003', 'harga_jual' => 18000, 'jenis_menu_id' => 1, 'kategori_menu_id' => 1],
    
    // Menu Catering (jenis_menu_id = 2)
    ['nama_menu' => 'Catering Paket A (50 porsi)', 'kode_menu' => 'CT-A', 'harga_jual' => 2000000, 'jenis_menu_id' => 2, 'kategori_menu_id' => 2],
    ['nama_menu' => 'Catering Paket B (100 porsi)', 'kode_menu' => 'CT-B', 'harga_jual' => 3500000, 'jenis_menu_id' => 2, 'kategori_menu_id' => 2],
];

try {
    foreach ($menuBaru as $menu) {
        // Cek apakah menu sudah ada
        $existing = DB::table('menu')->where('kode_menu', $menu['kode_menu'])->first();
        
        if ($existing) {
            echo "Menu {$menu['nama_menu']} ({$menu['kode_menu']}) sudah ada.\n";
        } else {
            // Buat menu baru
            $menuId = DB::table('menu')->insertGetId([
                'nama_menu' => $menu['nama_menu'],
                'kode_menu' => $menu['kode_menu'],
                'harga_jual' => $menu['harga_jual'],
                'jenis_menu_id' => $menu['jenis_menu_id'],
                'kategori_menu_id' => $menu['kategori_menu_id'] ?? 1,
                'status_aktif' => true,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            
            echo "✓ Menu {$menu['nama_menu']} ({$menu['kode_menu']}) berhasil dibuat dengan ID: {$menuId}\n";
        }
    }
    
    echo "\n=== SELESAI ===\n";
    echo "Menu baru berhasil dibuat. Silakan input resep melalui interface web.\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

?>