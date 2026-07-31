<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\PesananDinein;
use App\Models\ItemPesananDinein;
use App\Models\PembayaranDinein;
use App\Models\Produk as Menu;
use App\Models\BahanBaku;
use App\Models\MutasiStok;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use App\Services\BOMService;
use Illuminate\Support\Facades\DB;
use Exception;

class DineInService
{
    /**
     * Buka meja dan buat pesanan baru jika meja kosong
     */
    public function bukaMeja($mejaId, $staffId)
    {
        return DB::transaction(function () use ($mejaId, $staffId) {
            $meja = Meja::findOrFail($mejaId);
            
            if ($meja->status_meja_id == 4 || $meja->status === 'tidak_aktif') {
                throw new Exception("Meja {$meja->nomor_meja} sedang tidak aktif.");
            }

            // Cari pesanan aktif
            $pesanan = PesananDinein::where('meja_id', $meja->id)
                ->where(function($q) {
                    $q->where('status_pesanan_id', 1);
                    if (DB::getSchemaBuilder()->hasColumn('pesanan', 'status')) {
                        $q->orWhere('status', 'menunggu_pembayaran');
                    }
                })
                ->latest()
                ->first();

            if (!$pesanan) {
                $pesanan = PesananDinein::create([
                    'meja_id' => $meja->id,
                    'status' => 'menunggu_pembayaran',
                    'sub_status' => 'diproses',
                    'dibuka_oleh' => $staffId,
                    'dibuka_pada' => now(),
                ]);

                $updateData = ['status_meja_id' => 2]; // 2 = TERISI
                if (DB::getSchemaBuilder()->hasColumn('meja', 'status')) {
                    $updateData['status'] = 'terisi';
                }
                $meja->update($updateData);
            }

            return $pesanan;
        });
    }

    /**
     * Tambah pesanan item ke meja
     */
    public function tambahPesanan($pesananId, $items, $staffId)
    {
        return DB::transaction(function () use ($pesananId, $items, $staffId) {
            $pesanan = PesananDinein::findOrFail($pesananId);
            if ($pesanan->status !== 'menunggu_pembayaran') {
                throw new \Exception("Tidak bisa menambah pesanan ke meja yang sudah dibayar/selesai.");
            }

            // Validasi BOM ketersediaan bahan baku untuk item tambahan
            foreach ($items as $item) {
                if (!BOMService::cekKetersediaanBahan($item['menu_id'], $item['qty'])) {
                    $menu = Menu::find($item['menu_id']);
                    $namaMenu = $menu ? ($menu->nama_produk ?? $menu->nama) : 'Menu';
                    throw new \Exception("Gagal menambah item: Stok bahan baku untuk menu '{$namaMenu}' tidak mencukupi (Stok Kosong).");
                }
            }

            // Buat item pesanan & potong stok BOM secara atomik
            foreach ($items as $item) {
                ItemPesananDinein::create([
                    'pesanan_dinein_id' => $pesanan->id,
                    'menu_id' => $item['menu_id'],
                    'qty' => $item['qty'],
                    'catatan' => $item['catatan'] ?? null,
                    'diinput_oleh' => $staffId,
                    'diinput_pada' => now(),
                ]);

                BOMService::kurangiStokBahan($item['menu_id'], $item['qty'], $pesanan->id);
            }

            return true;
        });
    }

    /**
     * Buat pesanan baru secara langsung (Satu Pintu POS)
     */
    public function createOrder($mejaId, $namaKonsumen, $jumlahTamu, $items, $staffId)
    {
        return DB::transaction(function () use ($mejaId, $namaKonsumen, $jumlahTamu, $items, $staffId) {
            $meja = Meja::findOrFail($mejaId);
            
            if ($meja->status_meja_id == 4 || $meja->status === 'tidak_aktif') {
                throw new Exception("Meja {$meja->nomor_meja} sedang tidak aktif.");
            }

            // Cek apakah meja sedang terisi atau memiliki pesanan aktif
            $hasActiveOrder = PesananDinein::where('meja_id', $meja->id)
                ->where('status_pesanan_id', 1)
                ->exists();

            if ($meja->status_meja_id == 2 || $meja->status === 'terisi' || $hasActiveOrder) {
                throw new Exception("Meja {$meja->nomor_meja} saat ini sedang terisi (memiliki pesanan aktif). Silakan minta kasir menyelesaikan pesanan sebelum membuat pesanan baru.");
            }

            // Hitung total tagihan pesanan
            $subtotal = 0;
            foreach ($items as $item) {
                $menu = Menu::find($item['menu_id']);
                $hargaUnit = $menu ? ($menu->harga_jual ?? $menu->harga ?? 0) : 0;
                $subtotal += ($hargaUnit * $item['qty']);
            }
            
            $pajak = $subtotal * 0.10;
            $totalTagihan = $subtotal + $pajak;

            $lastDinein = PesananDinein::latest()->first();
            $lastId = $lastDinein ? $lastDinein->id : 0;
            $kodePesanan = 'DIN-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            // Selalu buat pesanan baru khusus transaksi ini
            $pesanan = PesananDinein::create([
                'meja_id' => $meja->id,
                'nama_konsumen' => $namaKonsumen,
                'jumlah_tamu' => $jumlahTamu,
                'status' => 'menunggu_pembayaran',
                'dibuka_oleh' => $staffId,
                'dibuka_pada' => now(),
                'kode_pesanan' => $kodePesanan,
                'total_tagihan' => $totalTagihan
            ]);

            $updateData = ['status_meja_id' => 2]; // 2 = TERISI
            if (DB::getSchemaBuilder()->hasColumn('meja', 'status')) {
                $updateData['status'] = 'terisi';
            }
            $meja->update($updateData);

            // Tambahkan item khusus untuk pesanan baru ini saja
            foreach ($items as $item) {
                ItemPesananDinein::create([
                    'pesanan_dinein_id' => $pesanan->id,
                    'menu_id'           => $item['menu_id'],
                    'qty'               => $item['qty'],
                    'catatan'           => $item['catatan'] ?? null,
                    'diinput_oleh'      => $staffId,
                    'diinput_pada'      => now(),
                ]);
            }

            // --- VALIDASI TERLEBIH DAHULU: Cek Ketersediaan Bahan Baku (BOM) ---
            foreach ($items as $item) {
                if (!BOMService::cekKetersediaanBahan($item['menu_id'], $item['qty'])) {
                    $menu = Menu::find($item['menu_id']);
                    $namaMenu = $menu ? ($menu->nama_produk ?? $menu->nama) : 'Menu';
                    throw new \Exception("Pesanan gagal: Stok bahan baku untuk menu '{$namaMenu}' tidak mencukupi (Stok Kosong).");
                }
            }

            // --- EKSEKUSI PENGURANGAN STOK ATOMIK VIA BOM ---
            foreach ($items as $item) {
                BOMService::kurangiStokBahan($item['menu_id'], $item['qty'], $pesanan->id);
            }

            // --- SYNC ke tabel Pesanan (normalized) agar kasir POS bisa membaca ---
            // Cek apakah sudah ada entry di pesanan untuk PesananDinein ini
            $pesananNorm = Pesanan::where('nomor_pesanan', $kodePesanan)->first();
            if (!$pesananNorm) {
                $pesananNorm = Pesanan::create([
                    'nomor_pesanan'      => $kodePesanan,
                    'tanggal_pesanan'    => now(),
                    'jenis_pesanan_id'   => 1, // Dine In
                    'meja_id'            => $meja->id,
                    'pelayan_id'         => $staffId,
                    'status_pesanan_id'  => 1, // Menunggu Pembayaran
                    'total_tagihan'      => $totalHarga,
                    'catatan'            => 'Pemesan: ' . $namaKonsumen,
                ]);

                foreach ($items as $item) {
                    $menu = Menu::find($item['menu_id']);
                    if ($menu) {
                        $harga = $menu->harga_jual ?? $menu->harga ?? 0;
                        DetailPesanan::create([
                            'pesanan_id'  => $pesananNorm->id,
                            'produk_id'   => $menu->id,
                            'jumlah'      => $item['qty'],
                            'harga_satuan'=> $harga,
                            'subtotal'    => $harga * $item['qty'],
                            'catatan'     => $item['catatan'] ?? null,
                        ]);
                    }
                }
            }

            // Simpan id pesanan normalized ke pesananDinein agar bisa di-link
            $pesanan->pesanan_id = $pesananNorm->id;

            return $pesanan;
        });
    }


    /**
     * Proses pembayaran & Potong Stok (Bahan Baku)
     */
    public function prosesPembayaran($pesananId, $metodeBayar, $total, $staffId)
    {
        return DB::transaction(function () use ($pesananId, $metodeBayar, $total, $staffId) {
            $pesanan = PesananDinein::with('items.menu.resep')->findOrFail($pesananId);
            
            if ($pesanan->status !== 'menunggu_pembayaran') {
                throw new Exception("Pesanan sudah dibayar atau selesai.");
            }

            // 1. Simpan pembayaran
            $pembayaran = PembayaranDinein::create([
                'pesanan_dinein_id' => $pesanan->id,
                'metode_bayar' => $metodeBayar,
                'total' => $total,
                'diproses_oleh' => $staffId,
                'diproses_pada' => now(),
                'status' => 'lunas',
            ]);

            // 2. Update status pesanan & meja
            $pesanan->update([
                'status' => 'lunas',
                'dibayar_pada' => now(),
            ]);

            $mejaUpdate = ['status_meja_id' => 1]; // 1 = TERSEDIA
            if (DB::getSchemaBuilder()->hasColumn('meja', 'status')) {
                $mejaUpdate['status'] = 'kosong';
            }
            if ($pesanan->meja) {
                $pesanan->meja->update($mejaUpdate);
            }

            // Update status Master Pesanan
            $masterPesanan = Pesanan::where('keterangan', 'Order_ID_Dinein:' . $pesanan->id)->first();
            if ($masterPesanan) {
                $masterPesanan->update([
                    'status_pembayaran' => 'lunas',
                    'status_pesanan' => 'selesai'
                ]);
            }

            // 3. Stok sudah dipotong saat pesanan diinput (createOrder), 
            // jadi di sini hanya mengupdate status pembayaran saja.

            return $pembayaran;
        });
    }

    /**
     * Set meja kembali kosong saat tamu pulang
     */
    public function kosongkanMeja($mejaId)
    {
        return DB::transaction(function () use ($mejaId) {
            $meja = Meja::findOrFail($mejaId);
            
            // Selesaikan pesanan yang lunas (opsional, tapi disarankan)
            $pesananAktif = PesananDinein::where('meja_id', $mejaId)
                ->where(function($q) {
                    $q->where('status_pesanan_id', 5); // 5 = Selesai
                    if (DB::getSchemaBuilder()->hasColumn('pesanan', 'status')) {
                        $q->orWhere('status', 'lunas');
                    }
                })
                ->get();
            foreach ($pesananAktif as $pesanan) {
                $pesanan->update(['status' => 'selesai']);
            }
            
            $meja->update(['status' => 'kosong']);
            return true;
        });
    }

    /**
     * Void Pesanan & Kembalikan Stok
     */
    public function voidPesanan($pesananId, $staffId)
    {
        return DB::transaction(function () use ($pesananId, $staffId) {
            $pesanan = PesananDinein::with('items.menu.resep')->findOrFail($pesananId);
            
            if ($pesanan->status === 'batal') {
                throw new \Exception("Pesanan ini sudah dibatalkan/void.");
            }

            // Kembalikan stok
            $bahanKebutuhan = [];
            foreach ($pesanan->items as $item) {
                $qtyPesan = $item->qty;
                $menu = $item->menu;

                if ($menu && $menu->resep) {
                    foreach ($menu->resep as $resep) {
                        $bahanId = $resep->bahan_baku_id;
                        $jumlahKebutuhan = $resep->jumlah_kebutuhan * $qtyPesan;

                        if (isset($bahanKebutuhan[$bahanId])) {
                            $bahanKebutuhan[$bahanId] += $jumlahKebutuhan;
                        } else {
                            $bahanKebutuhan[$bahanId] = $jumlahKebutuhan;
                        }
                    }
                }
            }

            foreach ($bahanKebutuhan as $bahanId => $totalKebutuhan) {
                $bahanBaku = BahanBaku::find($bahanId);
                if ($bahanBaku) {
                    $bahanBaku->stok += $totalKebutuhan;
                    $bahanBaku->save();

                    MutasiStok::create([
                        'bahan_baku_id' => $bahanBaku->id,
                        'user_id' => auth()->check() ? auth()->id() : 1,
                        'jenis_mutasi' => 'masuk',
                        'jumlah' => $totalKebutuhan,
                        'sisa_stok' => $bahanBaku->stok,
                        'keterangan' => 'Void/Refund POS Dine-In (Order #' . $pesanan->id . ')',
                    ]);
                }
            }

            // Update status Pesanan Dinein
            $pesanan->update(['status' => 'batal']);
            
            // Jika ada pembayaran terkait, update status menjadi void
            $pembayaran = PembayaranDinein::where('pesanan_dinein_id', $pesanan->id)->first();
            if ($pembayaran) {
                $pembayaran->update(['status' => 'void']);
            }

            // Update status Master Pesanan
            $masterPesanan = Pesanan::where('keterangan', 'Order_ID_Dinein:' . $pesanan->id)->first();
            if ($masterPesanan) {
                $masterPesanan->update([
                    'status_pesanan' => 'dibatalkan',
                    'status_pembayaran' => 'refund'
                ]);
            }

            // Kosongkan meja jika status mejanya masih berhubungan
            if ($pesanan->meja) {
                $mejaUpdate = ['status_meja_id' => 1];
                if (DB::getSchemaBuilder()->hasColumn('meja', 'status')) {
                    $mejaUpdate['status'] = 'kosong';
                }
                $pesanan->meja->update($mejaUpdate);
            }

            return true;
        });
    }
}
