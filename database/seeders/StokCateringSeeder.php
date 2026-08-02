<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StokCateringSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('stok_catering')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $jenisCat = DB::table('jenis_pesanan')->where('kode_jenis', 'CAT')->value('id');
        $pelangganId = DB::table('pelanggan')->value('id');

        // 1. Pastikan ada beberapa pesanan catering
        $pesanan = [
            [
                'nomor_pesanan' => 'CTR-20260725-001',
                'tanggal' => '2026-07-25 08:30:00',
                'status_pesanan_id' => 5, // Selesai
                'jumlah_sebelum_potongan' => 2375000,
                'jumlah_diskon' => 0,
                'jumlah_pajak' => 0,
                'biaya_pelayanan' => 0,
                'total_tagihan' => 2375000,
                'catatan' => 'Catering acara ulang tahun, Paket B 50 porsi.',
            ],
            [
                'nomor_pesanan' => 'CTR-20260729-001',
                'tanggal' => '2026-07-29 07:45:00',
                'status_pesanan_id' => 4, // Siap Disajikan
                'jumlah_sebelum_potongan' => 4750000,
                'jumlah_diskon' => 237500,
                'jumlah_pajak' => 0,
                'biaya_pelayanan' => 0,
                'total_tagihan' => 4512500,
                'catatan' => 'Catering rapat kantor, Paket A 100 porsi.',
            ],
        ];

        $pesananIds = [];
        foreach ($pesanan as $p) {
            $id = DB::table('pesanan')->insertGetId([
                'nomor_pesanan' => $p['nomor_pesanan'],
                'jenis_pesanan_id' => $jenisCat,
                'pelanggan_id' => $pelangganId,
                'status_pesanan_id' => $p['status_pesanan_id'],
                'tanggal_pesanan' => $p['tanggal'],
                'jumlah_sebelum_potongan' => $p['jumlah_sebelum_potongan'],
                'jumlah_diskon' => $p['jumlah_diskon'],
                'jumlah_pajak' => $p['jumlah_pajak'],
                'biaya_pelayanan' => $p['biaya_pelayanan'],
                'total_tagihan' => $p['total_tagihan'],
                'catatan' => $p['catatan'],
            ]);
            $pesananIds[$p['nomor_pesanan']] = $id;
        }

        // Ambil pesanan catering yang sudah ada (termasuk CTR-20260801-001)
        $existing = DB::table('pesanan')
            ->where('jenis_pesanan_id', $jenisCat)
            ->pluck('id', 'nomor_pesanan')
            ->toArray();

        $semuaPesanan = array_merge($existing, $pesananIds);

        // 2. Kebutuhan bahan baku per pesanan (kebutuhan/diterima/digunakan)
        $bahan = [
            'CTR-20260725-001' => [
                // 50 porsi, Selesai -> semua diterima & digunakan
                ['BB001', 5000, 5000, 5000],   // Beras 100g/porsi
                ['BB002', 4000, 4000, 4000],   // Ayam Broiler 80g/porsi
                ['BB015', 50, 50, 50],         // Telur 1/porsi
                ['BB012', 4, 4, 4],            // Minyak Goreng
                ['BB005', 800, 800, 800],      // Bawang Merah
                ['BB006', 500, 500, 500],      // Bawang Putih
                ['BB026', 300, 300, 300],      // Cabai Rawit
                ['BB036', 2, 2, 2],            // Kecap Manis
                ['BB024', 2000, 2000, 2000],   // Santan Kelapa
                ['BB041', 250, 250, 250],      // Garam
                ['BB025', 25, 25, 25],         // Air Mineral
                ['BB055', 50, 50, 50],         // Kotak Catering Mika
                ['BB056', 50, 50, 50],         // Sendok Plastik
            ],
            'CTR-20260729-001' => [
                // 100 porsi, Siap -> diterima penuh, sebagian digunakan
                ['BB001', 10000, 10000, 6000],
                ['BB020', 7000, 7000, 4200],   // Daging Sapi
                ['BB015', 100, 100, 60],
                ['BB012', 6, 6, 3.5],
                ['BB005', 1500, 1500, 900],
                ['BB006', 800, 800, 480],
                ['BB007', 600, 600, 360],      // Cabai Merah Keriting
                ['BB036', 3, 3, 2],
                ['BB038', 2, 2, 1],            // Saus Sambal
                ['BB024', 3000, 3000, 1800],
                ['BB041', 400, 400, 240],
                ['BB025', 50, 50, 30],
                ['BB055', 100, 100, 60],
                ['BB056', 100, 100, 60],
                ['BB058', 100, 100, 60],       // Daun Pisang
            ],
            'CTR-20260801-001' => [
                // 75 porsi, Menunggu -> belum diterima (tampil "Kurang")
                ['BB001', 7500, 0, 0],
                ['BB002', 6000, 0, 0],
                ['BB015', 75, 0, 0],
                ['BB012', 5, 0, 0],
                ['BB005', 1200, 0, 0],
                ['BB006', 600, 0, 0],
                ['BB026', 450, 0, 0],
                ['BB036', 3, 0, 0],
                ['BB024', 2500, 0, 0],
                ['BB041', 300, 0, 0],
                ['BB025', 38, 0, 0],
                ['BB055', 75, 0, 0],
                ['BB056', 75, 0, 0],
            ],
        ];

        $kodeToId = DB::table('bahan_baku')->pluck('id', 'kode_bahan')->toArray();

        foreach ($bahan as $nomorPesanan => $items) {
            if (! isset($semuaPesanan[$nomorPesanan])) {
                continue;
            }
            $pesananId = $semuaPesanan[$nomorPesanan];
            foreach ($items as [$kode, $kebutuhan, $diterima, $digunakan]) {
                $bahanId = $kodeToId[$kode] ?? null;
                if (! $bahanId) {
                    continue;
                }
                DB::table('stok_catering')->updateOrInsert(
                    ['pesanan_id' => $pesananId, 'bahan_baku_id' => $bahanId],
                    [
                        'kebutuhan' => $kebutuhan,
                        'diterima' => $diterima,
                        'digunakan' => $digunakan,
                    ]
                );
            }
        }

        $this->command->info('Seeder Stok Catering selesai: '.count($bahan).' pesanan catering diberi kebutuhan bahan.');
    }
}
