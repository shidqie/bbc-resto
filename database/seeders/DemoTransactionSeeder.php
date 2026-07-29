<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();

        // 1. TRUNCATE ALL TRANSACTION TABLES
        \App\Models\Pesanan::truncate();
        \App\Models\DetailPesanan::truncate();
        \App\Models\PesananDinein::truncate();
        \App\Models\ItemPesananDinein::truncate();
        \App\Models\PembayaranDinein::truncate();
        \App\Models\PesananCatering::truncate();
        \App\Models\PesananCateringDetail::truncate();
        \App\Models\PesananCateringAddon::truncate();
        \App\Models\PesananNasiBox::truncate();
        \App\Models\PesananNasiBoxDetail::truncate();
        \App\Models\Pengadaan::truncate();
        \App\Models\DetailPengadaan::truncate();
        \App\Models\MutasiStok::truncate();
        \App\Models\Pembayaran::truncate();
        \App\Models\BuktiPembayaran::truncate();
        \App\Models\PesananStatusLog::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 2. RESET STOK BAHAN BAKU TO 100
        $bahanBakus = \App\Models\BahanBaku::all();
        foreach ($bahanBakus as $bahan) {
            $bahan->update(['stok' => 100]);
        }

        $faker = \Faker\Factory::create('id_ID');

        // 3. CREATE 10 DINE IN TRANSACTIONS (POS)
        $mejas = \App\Models\Meja::all();
        $menus = \App\Models\Menu::all();
        if ($menus->count() > 0) {
            for ($i = 1; $i <= 10; $i++) {
                $total = 0;
                $details = [];
                $numItems = rand(1, 4);
                
                $pesanan = \App\Models\PesananDinein::create([
                    'kode_pesanan' => 'POS' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'meja_id' => $mejas->count() > 0 ? $mejas->random()->id : null,
                    'nama_konsumen' => $faker->name,
                    'jumlah_tamu' => rand(1, 4),
                    'status' => 'lunas', // 'menunggu_pembayaran', 'lunas', 'void'
                    'dibuka_oleh' => 1,
                    'dibuka_pada' => now()->subDays(rand(1, 14))->addHours(rand(10, 18)),
                    'dibayar_pada' => now()->subDays(rand(1, 14))->addHours(rand(18, 20)),
                ]);

                for ($j = 0; $j < $numItems; $j++) {
                    $menu = $menus->random();
                    $qty = rand(1, 3);
                    $subtotal = $menu->harga * $qty;
                    $total += $subtotal;
                    
                    \App\Models\ItemPesananDinein::create([
                        'pesanan_dinein_id' => $pesanan->id,
                        'menu_id' => $menu->id,
                        'qty' => $qty,
                        'status_sajian' => 1,
                        'catatan' => null,
                        'diinput_oleh' => 1,
                        'diinput_pada' => $pesanan->dibuka_pada,
                    ]);
                }

                $pajak = $total * 0.11;
                $totalTagihan = $total + $pajak;

                \App\Models\PembayaranDinein::create([
                    'pesanan_dinein_id' => $pesanan->id,
                    'metode_bayar' => collect(['cash', 'qris', 'kartu'])->random(),
                    'total' => $totalTagihan,
                    'diproses_oleh' => 1,
                    'diproses_pada' => $pesanan->dibayar_pada,
                    'status' => 'lunas',
                ]);
            }
        }

        // 4. CREATE 10 CATERING TRANSACTIONS
        $paketCaterings = \App\Models\PaketCatering::where('jenis_paket', 'catering')->get();
        if ($paketCaterings->count() > 0) {
            for ($i = 1; $i <= 10; $i++) {
                $paket = $paketCaterings->random();
                $porsi = rand(50, 200);
                $total = $paket->harga * $porsi;
                
                $statusList = ['ditinjau', 'dikonfirmasi', 'menunggu_pelunasan', 'diproses', 'menunggu_pengiriman', 'dikirim', 'selesai'];
                $status = $statusList[rand(0, count($statusList) - 1)];
                $statusBayar = 'belum_bayar';
                $dpAmount = 0;
                
                if (in_array($status, ['menunggu_pelunasan', 'diproses'])) {
                    $statusBayar = 'dp_terbayar';
                    $dpAmount = $total / 2;
                } elseif (in_array($status, ['menunggu_pengiriman', 'dikirim', 'selesai'])) {
                    $statusBayar = 'lunas';
                    $dpAmount = $total;
                }

                $pesananCat = \App\Models\PesananCatering::create([
                    'kode_pesanan' => 'CTR' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'nama_pemesan' => $faker->name,
                    'kontak' => $faker->phoneNumber,
                    'lokasi_acara' => $faker->address,
                    'metode_pengiriman' => 'delivery',
                    'ongkos_kirim' => 0,
                    'jarak_km' => rand(1, 15),
                    'tanggal_acara' => now()->addDays(rand(-5, 14)),
                    'paket_id' => $paket->id,
                    'jumlah_porsi' => $porsi,
                    'total_tagihan' => $total,
                    'dp_amount' => $dpAmount,
                    'status' => $status,
                    'status_bayar' => $statusBayar,
                ]);

                // We don't necessarily need details unless it breaks the view. Let's add dummy details if needed, skip for now.
            }
        }

        // 5. CREATE 10 NASI BOX TRANSACTIONS
        $paketNasibox = \App\Models\PaketCatering::where('jenis_paket', 'nasi_box')->get();
        if ($paketNasibox->count() > 0) {
            for ($i = 1; $i <= 10; $i++) {
                $paket = $paketNasibox->random();
                $box = rand(20, 100);
                $total = $paket->harga * $box;
                
                $pesananBox = \App\Models\PesananNasiBox::create([
                    'kode_pesanan' => 'NBX' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'nama_pemesan' => $faker->name,
                    'kontak' => $faker->phoneNumber,
                    'alamat' => $faker->address,
                    'metode_pengiriman' => 'pickup',
                    'ongkos_kirim' => 0,
                    'tanggal_acara' => now()->addDays(rand(-5, 14)),
                    'paket_id' => $paket->id,
                    'jumlah_box' => $box,
                    'total_tagihan' => $total,
                    'dp_amount' => $total,
                    'status' => 'selesai',
                    'status_bayar' => 'lunas',
                ]);
            }
        }

        // 6. CREATE 10 PENGADAAN TRANSACTIONS
        if ($bahanBakus->count() > 0) {
            for ($i = 1; $i <= 10; $i++) {
                $totalBiaya = 0;
                $details = [];
                $numItems = rand(2, 5);
                
                for ($j = 0; $j < $numItems; $j++) {
                    $bahan = $bahanBakus->random();
                    $qty = rand(5, 50);
                    // Use a rough estimate for price, 10000
                    $harga = 10000;
                    $subtotal = $qty * $harga;
                    $totalBiaya += $subtotal;
                    
                    $details[] = [
                        'bahan_baku_id' => $bahan->id,
                        'jumlah' => $qty,
                        'harga_satuan' => $harga,
                        'subtotal' => $subtotal
                    ];
                }

                $pengadaan = \App\Models\Pengadaan::create([
                    'nomor_pengadaan' => 'PGD-' . date('Ymd') . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'tanggal_pengadaan' => now()->subDays(rand(1, 14)),
                    'asal_pembelian' => 'Pasar ' . $faker->city,
                    'total_biaya' => $totalBiaya,
                    'catatan' => 'Dummy Pengadaan',
                    'user_id' => 1
                ]);

                foreach ($details as $d) {
                    $d['pengadaan_id'] = $pengadaan->id;
                    \App\Models\DetailPengadaan::create($d);
                    
                    \App\Models\MutasiStok::create([
                        'bahan_baku_id' => $d['bahan_baku_id'],
                        'jenis_mutasi' => 'masuk',
                        'jumlah' => $d['jumlah'],
                        'sisa_stok' => 100,
                        'keterangan' => 'Pengadaan ' . $pengadaan->nomor_pengadaan,
                        'user_id' => 1
                    ]);
                }
            }
        }
    }
}
