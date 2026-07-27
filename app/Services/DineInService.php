<?php

namespace App\Services;

use App\Models\Meja;
use App\Models\PesananDinein;
use App\Models\ItemPesananDinein;
use App\Models\PembayaranDinein;
use App\Models\Menu;
use App\Models\BahanBaku;
use App\Models\MutasiStok;
use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;

class DineInService
{
    /**
     * Buka meja dan buat pesanan baru jika meja kosong
     */
    public function bukaMeja($mejaId, $staffId)
    {
        return DB::transaction(function () use ($mejaId, $staffId) {
            $meja = Meja::findOrFail($mejaId);
            
            if ($meja->status !== 'kosong' && $meja->status !== 'menunggu_pembayaran') {
                throw new Exception("Meja tidak bisa dibuka karena statusnya {$meja->status}");
            }

            // Jika status masih kosong, buat pesanan baru
            if ($meja->status === 'kosong') {
                $pesanan = PesananDinein::create([
                    'meja_id' => $meja->id,
                    'status' => 'menunggu_pembayaran',
                    'sub_status' => 'diproses',
                    'dibuka_oleh' => $staffId,
                    'dibuka_pada' => now(),
                ]);

                $meja->update(['status' => 'menunggu_pembayaran']);
            } else {
                // Cari pesanan aktif
                $pesanan = PesananDinein::where('meja_id', $meja->id)
                    ->where('status', 'menunggu_pembayaran')
                    ->latest()
                    ->first();
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
                if (!Menu::cekKetersediaanBahan($item['menu_id'], $item['qty'])) {
                    $menu = Menu::find($item['menu_id']);
                    $namaMenu = $menu ? $menu->nama : 'Menu';
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

                Menu::kurangiStokBahan($item['menu_id'], $item['qty'], $pesanan->id);
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
            
            if ($meja->status !== 'kosong' && $meja->status !== 'menunggu_pembayaran') {
                throw new Exception("Meja tidak bisa dibuka karena statusnya {$meja->status}");
            }

            $lastDinein = PesananDinein::latest()->first();
            $lastId = $lastDinein ? $lastDinein->id : 0;
            $kodePesanan = 'DIN-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

            // Batalkan pesanan menggantung terdahulu pada meja ini jika ada
            PesananDinein::where('meja_id', $meja->id)
                ->where('status', 'menunggu_pembayaran')
                ->update(['status' => 'batal']);

            // Selalu buat pesanan baru khusus transaksi ini
            $pesanan = PesananDinein::create([
                'meja_id' => $meja->id,
                'nama_konsumen' => $namaKonsumen,
                'jumlah_tamu' => $jumlahTamu,
                'status' => 'menunggu_pembayaran',
                'dibuka_oleh' => $staffId,
                'dibuka_pada' => now(),
                'kode_pesanan' => $kodePesanan,
            ]);

            $meja->update(['status' => 'menunggu_pembayaran']);

            // Tambahkan item khusus untuk pesanan baru ini saja
            foreach ($items as $item) {
                ItemPesananDinein::create([
                    'pesanan_dinein_id' => $pesanan->id,
                    'menu_id' => $item['menu_id'],
                    'qty' => $item['qty'],
                    'catatan' => $item['catatan'] ?? null,
                    'diinput_oleh' => $staffId,
                    'diinput_pada' => now(),
                ]);
            }

            // --- VALIDASI TERLEBIH DAHULU: Cek Ketersediaan Bahan Baku (BOM) ---
            foreach ($items as $item) {
                if (!Menu::cekKetersediaanBahan($item['menu_id'], $item['qty'])) {
                    $menu = Menu::find($item['menu_id']);
                    $namaMenu = $menu ? $menu->nama : 'Menu';
                    throw new \Exception("Pesanan gagal: Stok bahan baku untuk menu '{$namaMenu}' tidak mencukupi (Stok Kosong).");
                }
            }

            // --- EKSEKUSI PENGURANGAN STOK ATOMIK VIA BOM ---
            foreach ($items as $item) {
                Menu::kurangiStokBahan($item['menu_id'], $item['qty'], $pesanan->id);
            }

            // --- SYNC KE MASTER PESANAN (Global Order List) ---
            $totalHarga = 0;
            foreach ($items as $item) {
                $menu = Menu::find($item['menu_id']);
                $totalHarga += ($menu->harga * $item['qty']);
            }

            $masterPesanan = Pesanan::where('keterangan', 'Order_ID_Dinein:' . $pesanan->id)->first();
            
            if (!$masterPesanan) {
                $lastPesanan = Pesanan::latest()->first();
                $lastIdMaster = $lastPesanan ? $lastPesanan->id : 0;
                $noPesanan = 'INV-' . date('Ymd') . '-' . str_pad($lastIdMaster + 1, 4, '0', STR_PAD_LEFT);
                
                $masterPesanan = Pesanan::create([
                    'no_pesanan' => $noPesanan,
                    'nama_pelanggan' => $namaKonsumen,
                    'no_meja' => $meja->nomor_meja,
                    'jenis_pesanan' => 'dine_in',
                    'tanggal_pesanan' => now(),
                    'jumlah_porsi' => collect($items)->sum('qty'),
                    'status_pesanan' => 'baru',
                    'user_id' => auth()->check() ? auth()->id() : $staffId,
                    'total_harga' => $totalHarga,
                    'keterangan' => 'Order_ID_Dinein:' . $pesanan->id
                ]);
            } else {
                $masterPesanan->update([
                    'jumlah_porsi' => $masterPesanan->jumlah_porsi + collect($items)->sum('qty'),
                    'total_harga' => $masterPesanan->total_harga + $totalHarga,
                ]);
            }

            foreach ($items as $item) {
                $menu = Menu::find($item['menu_id']);
                DetailPesanan::create([
                    'pesanan_id' => $masterPesanan->id,
                    'menu_id' => $menu->id,
                    'jumlah' => $item['qty'],
                    'harga_satuan' => $menu->harga,
                    'subtotal' => ($menu->harga * $item['qty']),
                    'catatan' => $item['catatan'] ?? null
                ]);
            }

            // Notifikasi Admin
            \App\Models\NotifikasiAdmin::buatNotifikasi(
                'Pesanan Resto Baru #' . $pesanan->kode_pesanan,
                "Pesanan Resto/Dine-In baru di Meja {$meja->nomor_meja} atas nama {$namaKonsumen} (Total: Rp " . number_format($totalHarga, 0, ',', '.') . ").",
                'pesanan_baru',
                '/pos/dine-in'
            );

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

            $pesanan->meja->update(['status' => 'kosong']);

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
            $pesananAktif = PesananDinein::where('meja_id', $mejaId)->where('status', 'lunas')->get();
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
            if ($pesanan->meja && $pesanan->meja->status !== 'kosong') {
                $pesanan->meja->update(['status' => 'kosong']);
            }

            return true;
        });
    }
}
