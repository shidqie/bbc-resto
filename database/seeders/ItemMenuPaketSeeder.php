<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\PilihanItemPaket;
use Illuminate\Database\Seeder;

class ItemMenuPaketSeeder extends Seeder
{
    public function run(): void
    {
        $existing = Menu::all()->keyBy(fn ($m) => strtolower($m->nama_menu));
        $created = collect();
        $seq = Menu::count();

        $findOrCreate = function (string $nama, int $jenisId, ?int $kategoriId) use (&$existing, &$created, &$seq) {
            $key = strtolower(trim($nama));
            if ($key === '') {
                return null;
            }
            if ($menu = $existing->get($key)) {
                return $menu->id;
            }
            if ($menu = $created->get($key)) {
                return $menu;
            }
            $seq++;
            $menu = Menu::create([
                'jenis_menu_id'    => $jenisId,
                'kategori_menu_id' => $kategoriId,
                'kode_menu'        => 'KMP' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT),
                'nama_menu'        => trim($nama),
                'harga_jual'       => 0,
                'status_aktif'     => 1,
            ]);
            $created->put($key, $menu->id);
            return $menu->id;
        };

        $pakets = Menu::whereIn('jenis_menu_id', [2, 3])
            ->with('komponen_paket.opsi')
            ->get();

        foreach ($pakets as $paket) {
            foreach ($paket->komponen_paket as $item) {
                $menuId = $findOrCreate($item->nama_item, $paket->jenis_menu_id, $paket->kategori_menu_id);
                if ($item->opsi->isEmpty() && $menuId) {
                    // item tanpa opsi tetap dibuatkan menu agar bisa diatur resepnya (di-link via nama)
                    continue;
                }
                foreach ($item->opsi as $opsi) {
                    $linkedId = $findOrCreate($opsi->nama_pilihan, $paket->jenis_menu_id, $paket->kategori_menu_id);
                    if ($linkedId) {
                        PilihanItemPaket::where('id', $opsi->id)
                            ->whereNull('menu_id')
                            ->update(['menu_id' => $linkedId]);
                    }
                }
            }
        }
    }
}
