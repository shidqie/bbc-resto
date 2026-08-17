<?php
use App\Models\BahanBaku;
use App\Models\Menu;
use App\Models\ResepMenu;
use App\Models\Satuan;

$data = [
    'Nasi Putih' => [
        ['Beras', 0.150, 'kg'],
        ['Air', 0.200, 'liter']
    ],
    'Ayam Goreng' => [
        ['Ayam Potong', 0.200, 'kg'], // modified to match the image provided by user
        ['Bawang Putih', 0.010, 'kg'],
        ['Ketumbar', 0.005, 'kg'],
        ['Garam', 0.005, 'kg'],
        ['Minyak Goreng', 0.020, 'liter']
    ],
    'Tumis Buncis' => [
        ['Buncis', 0.080, 'kg'],
        ['Bawang Merah', 0.010, 'kg'],
        ['Bawang Putih', 0.005, 'kg'],
        ['Garam', 0.003, 'kg'],
        ['Minyak Goreng', 0.010, 'liter']
    ],
    'Tahu Goreng' => [
        ['Tahu', 1, 'pcs'],
        ['Garam', 0.002, 'kg'],
        ['Minyak Goreng', 0.010, 'liter']
    ],
    'Sambal' => [
        ['Cabai Merah', 0.015, 'kg'],
        ['Cabai Rawit', 0.005, 'kg'],
        ['Tomat', 0.010, 'kg'],
        ['Bawang Merah', 0.005, 'kg'],
        ['Garam', 0.002, 'kg'],
        ['Minyak Goreng', 0.005, 'liter']
    ],
    'Kerupuk' => [
        ['Kerupuk', 1, 'pcs']
    ],
    'Air Mineral' => [
        ['Air Mineral Botol', 1, 'botol']
    ]
];

foreach ($data as $menuName => $reseps) {
    $menu = Menu::firstOrCreate(
        ['nama_menu' => $menuName],
        ['jenis_menu_id' => 1, 'kategori_menu_id' => 1, 'harga_jual' => 5000, 'status_aktif' => 1]
    );

    // clear existing recipe
    ResepMenu::where('menu_id', $menu->id)->delete();

    foreach ($reseps as $resep) {
        $namaBahan = $resep[0];
        $jumlah = $resep[1];
        $namaSatuan = $resep[2];

        $satuan = Satuan::firstOrCreate(
            ['nama_satuan' => $namaSatuan],
            ['singkatan' => strtolower($namaSatuan)]
        );

        $bahan = BahanBaku::firstOrCreate(
            ['nama_bahan' => $namaBahan],
            ['satuan_id' => $satuan->id, 'status_aktif' => 1]
        );

        ResepMenu::create([
            'menu_id' => $menu->id,
            'bahan_baku_id' => $bahan->id,
            'satuan_id' => $satuan->id,
            'jumlah_kebutuhan' => $jumlah,
            'dikonfirmasi' => true,
        ]);
    }
    echo "Resep for {$menuName} seeded.\n";
}
