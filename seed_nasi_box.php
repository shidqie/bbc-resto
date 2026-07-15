<?php
$kategoriNasiBox = \App\Models\KategoriMenu::where('nama', 'like', '%Nasi Box%')->first();
if(!$kategoriNasiBox) {
    $kategoriNasiBox = \App\Models\KategoriMenu::create(['nama' => 'Nasi Box', 'deskripsi' => 'Paket Nasi Box']);
}

\App\Models\Menu::firstOrCreate(
    ['nama' => 'Nasi Box Paket A'],
    [
        'kategori_menu_id' => $kategoriNasiBox->id,
        'harga' => 25000,
        'deskripsi' => 'Nasi Putih, Ayam Goreng, Tahu & Tempe Goreng, Lalapan, Sambal, Air Mineral',
        'status' => 'tersedia',
        'jenis_menu' => 'nasi_box'
    ]
);

\App\Models\Menu::firstOrCreate(
    ['nama' => 'Nasi Box Paket B'],
    [
        'kategori_menu_id' => $kategoriNasiBox->id,
        'harga' => 35000,
        'deskripsi' => 'Nasi Putih, Rendang Daging, Perkedel Kentang, Daun Singkong, Sambal Ijo, Pisang, Air Mineral',
        'status' => 'tersedia',
        'jenis_menu' => 'nasi_box'
    ]
);

\App\Models\Menu::firstOrCreate(
    ['nama' => 'Nasi Box Paket C'],
    [
        'kategori_menu_id' => $kategoriNasiBox->id,
        'harga' => 45000,
        'deskripsi' => 'Nasi Putih, Ikan Bakar Nila, Sayur Asem, Tahu & Tempe, Lalapan, Sambal Terasi, Jeruk, Air Mineral',
        'status' => 'tersedia',
        'jenis_menu' => 'nasi_box'
    ]
);

echo "Paket Nasi Box A, B, C berhasil di-seed!\n";
