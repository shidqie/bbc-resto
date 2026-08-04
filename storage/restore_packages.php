<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Katering A & B and Nasi Box A to E
$menuList = [
    ['id' => 101, 'kategori_menu_id' => 16, 'jenis_menu_id' => 2, 'kode_menu' => 'CAT-A', 'nama_menu' => 'Paket Catering A', 'harga_jual' => 47500, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true],
    ['id' => 102, 'kategori_menu_id' => 16, 'jenis_menu_id' => 2, 'kode_menu' => 'CAT-B', 'nama_menu' => 'Paket Catering B', 'harga_jual' => 42500, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true],
    ['id' => 105, 'kategori_menu_id' => 17, 'jenis_menu_id' => 3, 'kode_menu' => 'NB-A', 'nama_menu' => 'Nasi Box Paket A', 'harga_jual' => 47500, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true],
    ['id' => 106, 'kategori_menu_id' => 17, 'jenis_menu_id' => 3, 'kode_menu' => 'NB-B', 'nama_menu' => 'Nasi Box Paket B', 'harga_jual' => 35000, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true],
    ['id' => 107, 'kategori_menu_id' => 17, 'jenis_menu_id' => 3, 'kode_menu' => 'NB-C', 'nama_menu' => 'Nasi Box Paket C', 'harga_jual' => 30000, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true],
    ['id' => 108, 'kategori_menu_id' => 17, 'jenis_menu_id' => 3, 'kode_menu' => 'NB-D', 'nama_menu' => 'Nasi Box Paket D', 'harga_jual' => 25000, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true],
    ['id' => 109, 'kategori_menu_id' => 17, 'jenis_menu_id' => 3, 'kode_menu' => 'NB-E', 'nama_menu' => 'Nasi Box Paket E', 'harga_jual' => 20000, 'deskripsi' => 'Harga per porsi', 'status_aktif' => true],
];

foreach ($menuList as $p) {
    DB::table('menu')->updateOrInsert(['id' => $p['id']], $p);
}

// Clear all items for these menus
DB::table('item_paket')->whereIn('menu_id', [101, 102, 105, 106, 107, 108, 109])->delete();

$komponenList = [
    // --- Paket Catering A (menu_id 101) ---
    ['menu_id' => 101, 'nama_item' => 'Nasi Putih', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 1],
    ['menu_id' => 101, 'nama_item' => 'Aneka Sup', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
    ['menu_id' => 101, 'nama_item' => 'Aneka Olahan Daging Sapi', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 3],
    ['menu_id' => 101, 'nama_item' => 'Aneka Olahan Tambahan', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 4],
    ['menu_id' => 101, 'nama_item' => 'Sayuran', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 5],
    ['menu_id' => 101, 'nama_item' => 'Kerupuk Udang', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 6],
    ['menu_id' => 101, 'nama_item' => 'Air Mineral', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 7],
    ['menu_id' => 101, 'nama_item' => 'Stall', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 8],
    ['menu_id' => 101, 'nama_item' => 'Dessert', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 9],
    
    // --- Paket Catering B (menu_id 102) ---
    ['menu_id' => 102, 'nama_item' => 'Nasi Putih', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 1],
    ['menu_id' => 102, 'nama_item' => 'Aneka Sup', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
    ['menu_id' => 102, 'nama_item' => 'Aneka Olahan Ayam', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 3],
    ['menu_id' => 102, 'nama_item' => 'Aneka Olahan Tambahan', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 4],
    ['menu_id' => 102, 'nama_item' => 'Sayuran', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 5],
    ['menu_id' => 102, 'nama_item' => 'Kerupuk Udang', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 6],
    ['menu_id' => 102, 'nama_item' => 'Air Mineral', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 7],
    ['menu_id' => 102, 'nama_item' => 'Stall', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 8],
    ['menu_id' => 102, 'nama_item' => 'Dessert', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 9],

    // --- Nasi Box Paket A (menu_id 105) ---
    ['menu_id' => 105, 'nama_item' => 'Pilihan Nasi', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 1],
    ['menu_id' => 105, 'nama_item' => 'Pilihan Lauk Ayam', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
    ['menu_id' => 105, 'nama_item' => 'Pilihan Lauk Ikan', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 3],
    ['menu_id' => 105, 'nama_item' => 'Pilihan Lauk Tambahan', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 4],
    ['menu_id' => 105, 'nama_item' => 'Sayuran', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 5],
    ['menu_id' => 105, 'nama_item' => 'Lalapan', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 6],
    ['menu_id' => 105, 'nama_item' => 'Pelengkap', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 7],
    ['menu_id' => 105, 'nama_item' => 'Pilihan Buah', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 8],
    ['menu_id' => 105, 'nama_item' => 'Makanan Penutup', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 9],
    ['menu_id' => 105, 'nama_item' => 'Minuman', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 10],

    // --- Nasi Box Paket B (menu_id 106) ---
    ['menu_id' => 106, 'nama_item' => 'Pilihan Nasi', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 1],
    ['menu_id' => 106, 'nama_item' => 'Pilihan Lauk Utama', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
    ['menu_id' => 106, 'nama_item' => 'Lauk Tambahan', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 3],
    ['menu_id' => 106, 'nama_item' => 'Sayuran', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 4],
    ['menu_id' => 106, 'nama_item' => 'Lalapan', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 5],
    ['menu_id' => 106, 'nama_item' => 'Pelengkap', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 6],
    ['menu_id' => 106, 'nama_item' => 'Pilihan Buah', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 7],
    ['menu_id' => 106, 'nama_item' => 'Minuman', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 8],

    // --- Nasi Box Paket C (menu_id 107) ---
    ['menu_id' => 107, 'nama_item' => 'Nasi', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 1],
    ['menu_id' => 107, 'nama_item' => 'Pilihan Lauk Utama', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
    ['menu_id' => 107, 'nama_item' => 'Pilihan Lauk Tambahan', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 3],
    ['menu_id' => 107, 'nama_item' => 'Sayuran', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 4],
    ['menu_id' => 107, 'nama_item' => 'Lalapan', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 5],
    ['menu_id' => 107, 'nama_item' => 'Pelengkap', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 6],
    ['menu_id' => 107, 'nama_item' => 'Pilihan Buah', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 7],
    ['menu_id' => 107, 'nama_item' => 'Minuman', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 8],

    // --- Nasi Box Paket D (menu_id 108) ---
    ['menu_id' => 108, 'nama_item' => 'Nasi', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 1],
    ['menu_id' => 108, 'nama_item' => 'Pilihan Lauk Utama', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
    ['menu_id' => 108, 'nama_item' => 'Pilihan Lauk Tambahan', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 3],
    ['menu_id' => 108, 'nama_item' => 'Sayuran', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 4],
    ['menu_id' => 108, 'nama_item' => 'Lalapan', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 5],
    ['menu_id' => 108, 'nama_item' => 'Pelengkap', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 6],
    ['menu_id' => 108, 'nama_item' => 'Minuman', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 7],

    // --- Nasi Box Paket E (menu_id 109) ---
    ['menu_id' => 109, 'nama_item' => 'Nasi', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 1],
    ['menu_id' => 109, 'nama_item' => 'Pilihan Lauk Utama', 'tipe_item' => 'pilihan', 'minimum_pilihan' => 1, 'maksimum_pilihan' => 1, 'urutan' => 2],
    ['menu_id' => 109, 'nama_item' => 'Lalapan', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 3],
    ['menu_id' => 109, 'nama_item' => 'Pelengkap', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 4],
    ['menu_id' => 109, 'nama_item' => 'Minuman', 'tipe_item' => 'tetap', 'minimum_pilihan' => 0, 'maksimum_pilihan' => 0, 'urutan' => 5],
];

$pilihanList = [
    // Paket Catering A - Aneka Sup
    ['nama_item' => 'Aneka Sup', 'menu_id' => 101, 'pilihan' => ['Sup Kimlo', 'Sup Bakso', 'Sup Ayam Sosis']],
    ['nama_item' => 'Aneka Olahan Daging Sapi', 'menu_id' => 101, 'pilihan' => ['Sapi Teriyaki', 'Rendang', 'Bistik']],
    ['nama_item' => 'Aneka Olahan Tambahan', 'menu_id' => 101, 'pilihan' => ['Dori Asam Manis', 'Dori Saus Mentega', 'Sambal Goreng Ati Kentang']],
    ['nama_item' => 'Sayuran', 'menu_id' => 101, 'pilihan' => ['Salad Buah', 'Salad Sayuran', 'Gado-gado', 'Rujak']],
    ['nama_item' => 'Stall', 'menu_id' => 101, 'pilihan' => ['Bakso Tahu', 'Mi Kocok']],
    ['nama_item' => 'Dessert', 'menu_id' => 101, 'pilihan' => ['Buah Potong', 'Es Krim']],

    // Paket Catering B
    ['nama_item' => 'Aneka Sup', 'menu_id' => 102, 'pilihan' => ['Sup Kimlo', 'Sup Bakso', 'Sup Sosis']],
    ['nama_item' => 'Aneka Olahan Ayam', 'menu_id' => 102, 'pilihan' => ['Ayam Teriyaki', 'Ayam Suwir', 'Ayam Rica-rica']],
    ['nama_item' => 'Aneka Olahan Tambahan', 'menu_id' => 102, 'pilihan' => ['Dori Asam Manis', 'Dori Saus Mentega', 'Sambal Goreng Ati Kentang']],
    ['nama_item' => 'Sayuran', 'menu_id' => 102, 'pilihan' => ['Salad Buah', 'Salad Sayuran', 'Gado-gado', 'Rujak Buah']],
    ['nama_item' => 'Stall', 'menu_id' => 102, 'pilihan' => ['Bakso Tahu', 'Mi Kocok']],
    ['nama_item' => 'Dessert', 'menu_id' => 102, 'pilihan' => ['Buah Potong', 'Es Krim']],

    // Nasi Box Paket A
    ['nama_item' => 'Pilihan Nasi', 'menu_id' => 105, 'pilihan' => ['Nasi Putih', 'Nasi Liwet']],
    ['nama_item' => 'Pilihan Lauk Ayam', 'menu_id' => 105, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar']],
    ['nama_item' => 'Pilihan Lauk Ikan', 'menu_id' => 105, 'pilihan' => ['Ikan Goreng', 'Lele Goreng']],
    ['nama_item' => 'Pilihan Lauk Tambahan', 'menu_id' => 105, 'pilihan' => ['Telur Balado', 'Kentang Balado']],
    ['nama_item' => 'Pilihan Buah', 'menu_id' => 105, 'pilihan' => ['Melon', 'Semangka', 'Jeruk']],

    // Nasi Box Paket B
    ['nama_item' => 'Pilihan Nasi', 'menu_id' => 106, 'pilihan' => ['Nasi Putih', 'Nasi Liwet']],
    ['nama_item' => 'Pilihan Lauk Utama', 'menu_id' => 106, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar', 'Ikan Goreng']],
    ['nama_item' => 'Pilihan Buah', 'menu_id' => 106, 'pilihan' => ['Melon', 'Semangka', 'Jeruk']],

    // Nasi Box Paket C
    ['nama_item' => 'Pilihan Lauk Utama', 'menu_id' => 107, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar', 'Ikan Goreng']],
    ['nama_item' => 'Pilihan Lauk Tambahan', 'menu_id' => 107, 'pilihan' => ['Tempe, Tahu']],
    ['nama_item' => 'Pilihan Buah', 'menu_id' => 107, 'pilihan' => ['Melon', 'Semangka', 'Jeruk']],

    // Nasi Box Paket D
    ['nama_item' => 'Pilihan Lauk Utama', 'menu_id' => 108, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar']],
    ['nama_item' => 'Pilihan Lauk Tambahan', 'menu_id' => 108, 'pilihan' => ['Tempe, Tahu']],

    // Nasi Box Paket E
    ['nama_item' => 'Pilihan Lauk Utama', 'menu_id' => 109, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar']],
];

foreach ($komponenList as $k) {
    $itemPaketId = DB::table('item_paket')->insertGetId($k);

    // Look for pilihan
    foreach ($pilihanList as $p) {
        if ($p['menu_id'] === $k['menu_id'] && $p['nama_item'] === $k['nama_item']) {
            $urutan = 1;
            foreach ($p['pilihan'] as $namaPilihan) {
                DB::table('pilihan_item_paket')->insert([
                    'item_paket_id' => $itemPaketId,
                    'nama_pilihan' => $namaPilihan,
                    'urutan' => $urutan++
                ]);
            }
        }
    }
}
echo "Success!\n";
