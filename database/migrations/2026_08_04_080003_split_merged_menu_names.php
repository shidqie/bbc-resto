<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Pemisahan nama menu gabungan menjadi menu satuan (sesuai spesifikasi),
 * lalu menautkan komposisi Nasi Box Paket A (menu id 105).
 *
 *   Nasi Putih/Liwet        -> Nasi Putih, Nasi Liwet
 *   Ayam Goreng/Bakar       -> Ayam Goreng, Ayam Bakar
 *   Ikan/Lele Goreng        -> Ikan Goreng, Lele Goreng
 *   Telur Balado/Kentang Balado -> Telur Balado, Kentang Balado
 *   Melon, Semangka, Jeruk  -> Melon, Semangka, Jeruk
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $find = fn (string $nama) => DB::table('menu')->where('nama_menu', $nama)->value('id');
            $kategoriNasiBox = DB::table('kategori_menu')->where('nama_kategori', 'Nasi Box')->value('id') ?? 17;

            // Data migration: hanya berjalan jika data sumber (menu gabungan lama) ada.
            // Pada database baru (test/instalasi kosong) tidak ada data, jadi dilewati.
            $hasSource = collect(['Nasi Putih/Liwet', 'Ayam Goreng/Bakar', 'Ikan/Lele Goreng', 'Telur Balado/Kentang Balado', 'Melon, Semangka, Jeruk', 'Nasi Box Paket A'])
                ->contains(fn ($nama) => $find($nama) !== null);
            if (! $hasSource) {
                return;
            }

            // ── 1. Pastikan menu satuan yang hilang ada ──
            $make = function (string $nama, string $kode) use ($kategoriNasiBox, $find) {
                if ($find($nama)) {
                    return $find($nama);
                }
                return DB::table('menu')->insertGetId([
                    'jenis_menu_id' => 3,
                    'kategori_menu_id' => $kategoriNasiBox,
                    'kode_menu' => $kode,
                    'nama_menu' => $nama,
                    'harga_jual' => 0,
                    'status_aktif' => 1,
                    'dibuat_pada' => now(),
                    'diperbarui_pada' => now(),
                ]);
            };

            $leleGoreng = $make('Lele Goreng', 'KMP170');
            $telurBalado = $make('Telur Balado', 'KMP171');
            $kentangBalado = $make('Kentang Balado', 'KMP172');

            // ── 2. Repoint resep lama milik "Nasi Putih/Liwet" (143) ke Nasi Putih (58) ──
            $nasiPutih = $find('Nasi Putih');
            DB::table('resep_menu')->where('menu_id', $find('Nasi Putih/Liwet'))->update(['menu_id' => $nasiPutih]);

            // ── 3. Tautkan komposisi Nasi Box Paket A (menu 105) ──
            $paketA = $find('Nasi Box Paket A');
            if ($paketA) {
                $item = fn (string $nama) => DB::table('item_paket')->where('menu_id', $paketA)->where('nama_item', $nama)->first();

                $linkTetap = function (string $namaItem, string $namaMenu) use ($item) {
                    $row = $item($namaItem);
                    if (! $row) {
                        return;
                    }
                    $menuId = DB::table('menu')->where('nama_menu', $namaMenu)->value('id');
                    DB::table('item_paket')->where('id', $row->id)->update(['menu_id_terkait' => $menuId]);
                };

                $linkPilihan = function (string $namaItem, string $namaGroup, array $namaMenus) use ($item) {
                    $row = $item($namaItem);
                    if (! $row) {
                        return;
                    }
                    DB::table('item_paket')->where('id', $row->id)->update(['nama_item' => $namaGroup]);
                    $urutan = 1;
                    foreach ($namaMenus as $namaMenu) {
                        $menuId = DB::table('menu')->where('nama_menu', $namaMenu)->value('id');
                        if (! $menuId) {
                            continue;
                        }
                        DB::table('pilihan_item_paket')->insert([
                            'item_paket_id' => $row->id,
                            'nama_pilihan' => $namaMenu,
                            'menu_id' => $menuId,
                            'jumlah' => 1,
                            'satuan_sajian' => 'porsi',
                            'urutan' => $urutan++,
                            'dibuat_pada' => now(),
                            'diperbarui_pada' => now(),
                        ]);
                    }
                };

                $linkPilihan('Nasi Putih/Liwet', 'Pilihan Nasi', ['Nasi Putih', 'Nasi Liwet']);
                $linkPilihan('Ayam Goreng/Bakar', 'Pilihan Ayam', ['Ayam Goreng', 'Ayam Bakar']);
                $linkPilihan('Ikan/Lele Goreng', 'Pilihan Ikan', ['Ikan Goreng', 'Lele Goreng']);
                $linkPilihan('Telur Balado/Kentang Balado', 'Pilihan Balado', ['Telur Balado', 'Kentang Balado']);
                $linkPilihan('Melon, Semangka, Jeruk', 'Pilihan Buah', ['Melon', 'Semangka', 'Jeruk']);

                $linkTetap('Karedok', 'Karedok');
                $linkTetap('Lalapan', 'Lalapan');
                $linkTetap('Sambal', 'Sambal');
                $linkTetap('Kerupuk', 'Kerupuk');
                $linkTetap('Puding', 'Puding');
                $linkTetap('Air Mineral', 'Air Mineral');
            }

            // ── 4. Hapus placeholder menu gabungan yang sudah tidak dipakai ──
            $mergedNames = ['Nasi Putih/Liwet', 'Ayam Goreng/Bakar', 'Ikan/Lele Goreng', 'Telur Balado/Kentang Balado', 'Melon, Semangka, Jeruk'];
            foreach ($mergedNames as $nama) {
                $id = $find($nama);
                if ($id) {
                    DB::table('resep_menu')->where('menu_id', $id)->delete();
                    DB::table('pilihan_item_paket')->where('menu_id', $id)->delete();
                    DB::table('item_paket')->where('menu_id', $id)->delete();
                    DB::table('menu')->where('id', $id)->delete();
                }
            }
        });
    }

    public function down(): void
    {
        // Data migration one-way; tidak perlu rollback otomatis.
    }
};
