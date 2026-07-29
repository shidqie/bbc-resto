<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\KategoriBahan;
use App\Models\Satuan;

$kategoris = ['Daging', 'Seafood', 'Sayuran', 'Bumbu', 'Minuman', 'Bahan Kering', 'Lainnya'];
foreach ($kategoris as $k) {
    KategoriBahan::firstOrCreate(['nama_kategori' => $k]);
}

$satuans = [
    ['nama_satuan' => 'Gram', 'singkatan' => 'gr'],
    ['nama_satuan' => 'Kilogram', 'singkatan' => 'kg'],
    ['nama_satuan' => 'Mililiter', 'singkatan' => 'ml'],
    ['nama_satuan' => 'Liter', 'singkatan' => 'L'],
    ['nama_satuan' => 'Pcs', 'singkatan' => 'Pcs'],
    ['nama_satuan' => 'Buah', 'singkatan' => 'Buah'],
    ['nama_satuan' => 'Butir', 'singkatan' => 'Butir'],
    ['nama_satuan' => 'Lembar', 'singkatan' => 'Lembar'],
    ['nama_satuan' => 'Ikat', 'singkatan' => 'Ikat'],
    ['nama_satuan' => 'Botol', 'singkatan' => 'Botol'],
    ['nama_satuan' => 'Kaleng', 'singkatan' => 'Kaleng'],
    ['nama_satuan' => 'Bungkus', 'singkatan' => 'Bungkus'],
    ['nama_satuan' => 'Pack', 'singkatan' => 'Pack'],
    ['nama_satuan' => 'Sachet', 'singkatan' => 'Sachet'],
    ['nama_satuan' => 'Cup', 'singkatan' => 'Cup'],
    ['nama_satuan' => 'Gelas', 'singkatan' => 'Gelas'],
    ['nama_satuan' => 'Sendok Makan', 'singkatan' => 'sdm'],
    ['nama_satuan' => 'Sendok Teh', 'singkatan' => 'sdt'],
];

foreach ($satuans as $s) {
    Satuan::firstOrCreate(['nama_satuan' => $s['nama_satuan']], ['singkatan' => $s['singkatan']]);
}

echo "Seed Success\n";
