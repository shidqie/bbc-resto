<?php
$menus = \App\Models\Menu::whereIn('jenis_menu', ['catering', 'nasi_box'])->get();
echo "Menus catering/nasi_box count: " . $menus->count() . "\n";
foreach($menus as $m) {
    $bomCount = \App\Models\ResepMenu::where('menu_id', $m->id)->count();
    if($bomCount == 0) {
        echo "WARNING: Menu " . $m->nama . " has NO BOM\n";
    }
}
$paketCateringCount = \App\Models\PaketCatering::count();
echo "Paket Catering count: " . $paketCateringCount . "\n";
