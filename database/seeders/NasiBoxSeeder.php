<?php

namespace Database\Seeders;

use App\Models\KategoriMenu;
use App\Models\KomponenPaket;
use App\Models\Menu;
use App\Models\PilihanKomponenPaket;
use Illuminate\Database\Seeder;

class NasiBoxSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriNasibox = KategoriMenu::firstOrCreate(
            ['nama_kategori' => 'Nasi Box'],
            ['status_aktif' => true]
        );

        $dataPaket = [
            [
                'nama' => 'Paket Nasi Box A',
                'kode' => 'BOX001A',
                'harga' => 25000,
                'deskripsi' => 'Paket hemat ayam goreng dengan tahu/tempe, lengkap dengan sambal dan lalapan.',
                'komponen' => [
                    ['nama' => 'Nasi Putih', 'tipe' => 'tetap', 'urutan' => 1, 'pilihan' => []],
                    ['nama' => 'Lauk Utama', 'tipe' => 'pilihan', 'urutan' => 2, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar', 'Rendang Daging']],
                    ['nama' => 'Lauk Pendamping', 'tipe' => 'pilihan', 'urutan' => 3, 'pilihan' => ['Tahu Goreng', 'Tempe Goreng', 'Perkedel Kentang']],
                    ['nama' => 'Sayuran', 'tipe' => 'pilihan', 'urutan' => 4, 'pilihan' => ['Sayur Asem', 'Sayur Nangka']],
                    ['nama' => 'Sambal', 'tipe' => 'pilihan', 'urutan' => 5, 'pilihan' => ['Sambal Terasi', 'Sambal Ijo']],
                    ['nama' => 'Pelengkap', 'tipe' => 'tetap', 'urutan' => 6, 'pilihan' => ['Lalapan', 'Kerupuk']],
                    ['nama' => 'Minuman', 'tipe' => 'tetap', 'urutan' => 7, 'pilihan' => ['Air Mineral Gelas']],
                ],
            ],
            [
                'nama' => 'Paket Nasi Box B',
                'kode' => 'BOX001B',
                'harga' => 20000,
                'deskripsi' => 'Paket ayam goreng hemat dengan sayur dan sambal, tanpa nasi box premium.',
                'komponen' => [
                    ['nama' => 'Nasi Putih', 'tipe' => 'tetap', 'urutan' => 1, 'pilihan' => []],
                    ['nama' => 'Lauk Utama', 'tipe' => 'pilihan', 'urutan' => 2, 'pilihan' => ['Ayam Goreng', 'Ayam Bakar']],
                    ['nama' => 'Lauk Pendamping', 'tipe' => 'pilihan', 'urutan' => 3, 'pilihan' => ['Tahu Goreng', 'Tempe Goreng']],
                    ['nama' => 'Sayuran', 'tipe' => 'pilihan', 'urutan' => 4, 'pilihan' => ['Sayur Asem', 'Sayur Nangka']],
                    ['nama' => 'Sambal', 'tipe' => 'tetap', 'urutan' => 5, 'pilihan' => ['Sambal Terasi']],
                    ['nama' => 'Minuman', 'tipe' => 'tetap', 'urutan' => 6, 'pilihan' => ['Air Mineral Gelas']],
                ],
            ],
        ];

        foreach ($dataPaket as $p) {
            $paket = Menu::firstOrCreate(
                ['kode_menu' => $p['kode']],
                [
                    'nama_menu' => $p['nama'],
                    'jenis_menu_id' => 3, // NASI_BOX
                    'kategori_menu_id' => $kategoriNasibox->id,
                    'harga_jual' => $p['harga'],
                    'deskripsi' => $p['deskripsi'],
                    'status_aktif' => true,
                ]
            );

            // Hapus komponen lama lalu buat ulang agar seeder idempoten & selalu terbaru
            $paket->komponen_paket()->delete();

            foreach ($p['komponen'] as $idx => $k) {
                $tipe = $k['tipe'] === 'pilihan' ? 'pilihan' : 'tetap';
                $komponen = KomponenPaket::create([
                    'menu_id' => $paket->id,
                    'nama_komponen' => $k['nama'],
                    'tipe_komponen' => $tipe,
                    'minimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                    'maksimum_pilihan' => $tipe === 'pilihan' ? 1 : 0,
                    'urutan' => $k['urutan'],
                ]);

                foreach ($k['pilihan'] as $i => $namaPilihan) {
                    PilihanKomponenPaket::create([
                        'komponen_paket_id' => $komponen->id,
                        'nama_pilihan' => $namaPilihan,
                        'urutan' => $i + 1,
                    ]);
                }
            }
        }

        $this->command->info('Paket Nasi Box A dan B berhasil disiapkan.');
    }
}
