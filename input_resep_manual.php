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

// Function untuk input resep
function inputResep($menuKode, $resepData) {
    // Cari menu berdasarkan kode
    $menu = DB::table('menu')->where('kode_menu', $menuKode)->first();
    if (!$menu) {
        echo "Menu dengan kode $menuKode tidak ditemukan.\n";
        return false;
    }
    
    // Hapus resep lama jika ada
    DB::table('resep_menu')->where('menu_id', $menu->id)->delete();
    
    echo "Input resep untuk: {$menu->nama_menu}\n";
    
    foreach ($resepData as $item) {
        $bahanBaku = DB::table('bahan_baku')->where('nama_bahan', $item['bahan'])->first();
        if (!$bahanBaku) {
            echo "- Bahan '{$item['bahan']}' tidak ditemukan, skip.\n";
            continue;
        }
        
        // Convert jumlah ke satuan dasar
        $jumlahSatuan = convertToBaseSatuan($item['jumlah'], $item['satuan'], $bahanBaku->satuan_id);
        
        DB::table('resep_menu')->insert([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahanBaku->id,
            'jumlah' => $jumlahSatuan,
            'satuan_id' => $bahanBaku->satuan_id,
            'dikonfirmasi' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        echo "- {$item['bahan']}: {$item['jumlah']} {$item['satuan']} ✓\n";
    }
    
    echo "Resep untuk {$menu->nama_menu} berhasil diinput!\n\n";
    return true;
}

function convertToBaseSatuan($jumlah, $satuanInput, $satuanIdBahanBaku) {
    switch($satuanInput) {
        case 'kg':
            return $jumlah * 1000; // ke gram
        case 'liter':
        case 'l':
            return $jumlah * 1000; // ke ml
        default:
            return $jumlah;
    }
}

// Buat menu baru jika belum ada
function buatMenuBaru($namaMenu, $kodeMenu, $harga, $jenisMenuId, $kategoriMenuId = 1) {
    $existing = DB::table('menu')->where('kode_menu', $kodeMenu)->first();
    if ($existing) {
        return $existing->id;
    }
    
    return DB::table('menu')->insertGetId([
        'nama_menu' => $namaMenu,
        'kode_menu' => $kodeMenu,
        'harga_jual' => $harga,
        'jenis_menu_id' => $jenisMenuId,
        'kategori_menu_id' => $kategoriMenuId,
        'status_aktif' => true,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ]);
}

echo "=== MULAI INPUT RESEP MENU BBC RESTO ===\n\n";

try {
    // 1. RESEP NASI BOX PAKET A
    inputResep('NB-A', [
        ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
        ['bahan' => 'Ayam Broiler', 'jumlah' => 100, 'satuan' => 'g'],
        ['bahan' => 'Ikan Gurame', 'jumlah' => 80, 'satuan' => 'g'],
        ['bahan' => 'Telur Ayam', 'jumlah' => 0.5, 'satuan' => 'buah'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 50, 'satuan' => 'ml'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 25, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 20, 'satuan' => 'g'],
        ['bahan' => 'Tomat', 'jumlah' => 30, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 5, 'satuan' => 'g'],
        ['bahan' => 'Kemangi', 'jumlah' => 0.1, 'satuan' => 'ikat'],
        ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
    ]);

    // 2. RESEP NASI BOX PAKET B
    inputResep('NB-B', [
        ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
        ['bahan' => 'Ayam Broiler', 'jumlah' => 100, 'satuan' => 'g'],
        ['bahan' => 'Tempe', 'jumlah' => 0.5, 'satuan' => 'buah'],
        ['bahan' => 'Tahu Putih', 'jumlah' => 0.5, 'satuan' => 'buah'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 40, 'satuan' => 'ml'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 20, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 10, 'satuan' => 'g'],
        ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Tomat', 'jumlah' => 25, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 4, 'satuan' => 'g'],
        ['bahan' => 'Kemangi', 'jumlah' => 0.1, 'satuan' => 'ikat'],
        ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
    ]);

    // 3. RESEP NASI BOX PAKET C
    inputResep('NB-C', [
        ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
        ['bahan' => 'Ayam Broiler', 'jumlah' => 80, 'satuan' => 'g'],
        ['bahan' => 'Tempe', 'jumlah' => 0.3, 'satuan' => 'buah'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 35, 'satuan' => 'ml'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 8, 'satuan' => 'g'],
        ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 10, 'satuan' => 'g'],
        ['bahan' => 'Tomat', 'jumlah' => 20, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 3, 'satuan' => 'g'],
        ['bahan' => 'Kemangi', 'jumlah' => 0.08, 'satuan' => 'ikat'],
        ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
    ]);

    // 4. RESEP NASI BOX PAKET D
    inputResep('NB-D', [
        ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
        ['bahan' => 'Ayam Broiler', 'jumlah' => 70, 'satuan' => 'g'],
        ['bahan' => 'Tempe', 'jumlah' => 0.25, 'satuan' => 'buah'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 30, 'satuan' => 'ml'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 12, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 6, 'satuan' => 'g'],
        ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 8, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 2.5, 'satuan' => 'g'],
        ['bahan' => 'Kemangi', 'jumlah' => 0.05, 'satuan' => 'ikat'],
        ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
    ]);

    // 5. RESEP NASI BOX PAKET E
    inputResep('NB-E', [
        ['bahan' => 'Beras', 'jumlah' => 150, 'satuan' => 'g'],
        ['bahan' => 'Ayam Broiler', 'jumlah' => 60, 'satuan' => 'g'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 25, 'satuan' => 'ml'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 10, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 5, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 2, 'satuan' => 'g'],
        ['bahan' => 'Kemangi', 'jumlah' => 0.03, 'satuan' => 'ikat'],
        ['bahan' => 'Kotak Catering Mika', 'jumlah' => 1, 'satuan' => 'pcs'],
    ]);
    
    echo "\n=== INPUT MENU DINE IN ===\n";
    
    // Buat menu dine-in baru jika belum ada
    buatMenuBaru('Nasi Liwet Komplit', 'NL-001', 35000, 1, 1);
    inputResep('NL-001', [
        ['bahan' => 'Beras', 'jumlah' => 200, 'satuan' => 'g'],
        ['bahan' => 'Santan Kelapa Instan', 'jumlah' => 300, 'satuan' => 'ml'],
        ['bahan' => 'Ayam Kampung', 'jumlah' => 150, 'satuan' => 'g'],
        ['bahan' => 'Daun Salam', 'jumlah' => 5, 'satuan' => 'g'],
        ['bahan' => 'Serai', 'jumlah' => 10, 'satuan' => 'g'],
        ['bahan' => 'Lengkuas', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 20, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 8, 'satuan' => 'g'],
    ]);
    
    buatMenuBaru('Ayam Goreng Kalasan', 'AY-001', 28000, 1, 1);
    inputResep('AY-001', [
        ['bahan' => 'Ayam Broiler', 'jumlah' => 250, 'satuan' => 'g'],
        ['bahan' => 'Kunyit', 'jumlah' => 10, 'satuan' => 'g'],
        ['bahan' => 'Lengkuas', 'jumlah' => 8, 'satuan' => 'g'],
        ['bahan' => 'Daun Salam', 'jumlah' => 3, 'satuan' => 'g'],
        ['bahan' => 'Gula Merah', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 5, 'satuan' => 'g'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 100, 'satuan' => 'ml'],
    ]);
    
    buatMenuBaru('Gurame Bakar Sambal Dabu', 'IK-001', 45000, 1, 1);
    inputResep('IK-001', [
        ['bahan' => 'Ikan Gurame', 'jumlah' => 300, 'satuan' => 'g'],
        ['bahan' => 'Tomat', 'jumlah' => 50, 'satuan' => 'g'],
        ['bahan' => 'Cabai Rawit', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 25, 'satuan' => 'g'],
        ['bahan' => 'Jeruk Nipis', 'jumlah' => 0.5, 'satuan' => 'buah'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 3, 'satuan' => 'g'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 50, 'satuan' => 'ml'],
    ]);
    
    buatMenuBaru('Bebek Goreng Kremes', 'BE-001', 38000, 1, 1);
    inputResep('BE-001', [
        ['bahan' => 'Daging Bebek', 'jumlah' => 200, 'satuan' => 'g'],
        ['bahan' => 'Tepung Terigu', 'jumlah' => 30, 'satuan' => 'g'],
        ['bahan' => 'Kunyit', 'jumlah' => 8, 'satuan' => 'g'],
        ['bahan' => 'Ketumbar', 'jumlah' => 5, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 12, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 6, 'satuan' => 'g'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 80, 'satuan' => 'ml'],
    ]);
    
    buatMenuBaru('Sate Ayam (10 tusuk)', 'ST-001', 25000, 1, 1);
    inputResep('ST-001', [
        ['bahan' => 'Ayam Broiler', 'jumlah' => 200, 'satuan' => 'g'],
        ['bahan' => 'Kecap Manis', 'jumlah' => 30, 'satuan' => 'ml'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 10, 'satuan' => 'g'],
        ['bahan' => 'Cabai Rawit', 'jumlah' => 8, 'satuan' => 'g'],
        ['bahan' => 'Gula Merah', 'jumlah' => 20, 'satuan' => 'g'],
        ['bahan' => 'Tusuk Sate', 'jumlah' => 10, 'satuan' => 'pcs'],
    ]);
    
    buatMenuBaru('Sup Buntut Sapi', 'SP-001', 42000, 1, 1);
    inputResep('SP-001', [
        ['bahan' => 'Iga Sapi', 'jumlah' => 250, 'satuan' => 'g'],
        ['bahan' => 'Tomat', 'jumlah' => 40, 'satuan' => 'g'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 20, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 15, 'satuan' => 'g'],
        ['bahan' => 'Serai', 'jumlah' => 8, 'satuan' => 'g'],
        ['bahan' => 'Daun Salam', 'jumlah' => 3, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 5, 'satuan' => 'g'],
        ['bahan' => 'Merica Bubuk', 'jumlah' => 2, 'satuan' => 'g'],
    ]);
    
    // Menu Minuman
    buatMenuBaru('Es Teh Manis', 'MN-001', 8000, 1, 1);
    inputResep('MN-001', [
        ['bahan' => 'Teh Celup', 'jumlah' => 2, 'satuan' => 'buah'],
        ['bahan' => 'Gula Pasir', 'jumlah' => 25, 'satuan' => 'g'],
        ['bahan' => 'Air Mineral', 'jumlah' => 300, 'satuan' => 'ml'],
    ]);
    
    buatMenuBaru('Kopi Susu Gula Aren', 'MN-002', 15000, 1, 1);
    inputResep('MN-002', [
        ['bahan' => 'Kopi Bubuk', 'jumlah' => 20, 'satuan' => 'g'],
        ['bahan' => 'Susu UHT', 'jumlah' => 100, 'satuan' => 'ml'],
        ['bahan' => 'Sirup Gula Aren', 'jumlah' => 30, 'satuan' => 'ml'],
        ['bahan' => 'Air Mineral', 'jumlah' => 200, 'satuan' => 'ml'],
    ]);
    
    buatMenuBaru('Jus Alpukat', 'MN-003', 18000, 1, 1);
    inputResep('MN-003', [
        ['bahan' => 'Alpukat Mentega', 'jumlah' => 1, 'satuan' => 'buah'],
        ['bahan' => 'Susu UHT', 'jumlah' => 150, 'satuan' => 'ml'],
        ['bahan' => 'Gula Pasir', 'jumlah' => 30, 'satuan' => 'g'],
        ['bahan' => 'Air Mineral', 'jumlah' => 100, 'satuan' => 'ml'],
    ]);
    
    echo "\n=== INPUT MENU CATERING ===\n";
    
    // Paket Catering
    buatMenuBaru('Catering Paket A (50 porsi)', 'CT-A', 2000000, 2, 2);
    inputResep('CT-A', [
        ['bahan' => 'Beras', 'jumlah' => 7500, 'satuan' => 'g'],
        ['bahan' => 'Ayam Broiler', 'jumlah' => 5000, 'satuan' => 'g'],
        ['bahan' => 'Ikan Gurame', 'jumlah' => 4000, 'satuan' => 'g'],
        ['bahan' => 'Telur Ayam', 'jumlah' => 25, 'satuan' => 'buah'],
        ['bahan' => 'Tempe', 'jumlah' => 25, 'satuan' => 'buah'],
        ['bahan' => 'Tahu Putih', 'jumlah' => 25, 'satuan' => 'buah'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 2500, 'satuan' => 'ml'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 1250, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 750, 'satuan' => 'g'],
        ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 1000, 'satuan' => 'g'],
        ['bahan' => 'Tomat', 'jumlah' => 1500, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 250, 'satuan' => 'g'],
        ['bahan' => 'Kemangi', 'jumlah' => 5, 'satuan' => 'ikat'],
        ['bahan' => 'Kotak Catering Mika', 'jumlah' => 50, 'satuan' => 'pcs'],
    ]);
    
    buatMenuBaru('Catering Paket B (100 porsi)', 'CT-B', 3500000, 2, 2);
    inputResep('CT-B', [
        ['bahan' => 'Beras', 'jumlah' => 15000, 'satuan' => 'g'],
        ['bahan' => 'Ayam Broiler', 'jumlah' => 8000, 'satuan' => 'g'],
        ['bahan' => 'Daging Sapi', 'jumlah' => 5000, 'satuan' => 'g'],
        ['bahan' => 'Tempe', 'jumlah' => 50, 'satuan' => 'buah'],
        ['bahan' => 'Tahu Putih', 'jumlah' => 50, 'satuan' => 'buah'],
        ['bahan' => 'Minyak Goreng', 'jumlah' => 4000, 'satuan' => 'ml'],
        ['bahan' => 'Bawang Merah', 'jumlah' => 2000, 'satuan' => 'g'],
        ['bahan' => 'Bawang Putih', 'jumlah' => 1200, 'satuan' => 'g'],
        ['bahan' => 'Cabai Merah Keriting', 'jumlah' => 1500, 'satuan' => 'g'],
        ['bahan' => 'Tomat', 'jumlah' => 2500, 'satuan' => 'g'],
        ['bahan' => 'Garam Dapur', 'jumlah' => 400, 'satuan' => 'g'],
        ['bahan' => 'Kemangi', 'jumlah' => 10, 'satuan' => 'ikat'],
        ['bahan' => 'Kotak Catering Mika', 'jumlah' => 100, 'satuan' => 'pcs'],
    ]);

    echo "\n=== SELESAI ===\n";
    echo "Semua resep berhasil diinput ke sistem!\n";
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>