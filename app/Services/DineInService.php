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
                throw new Exception("Tidak bisa menambah pesanan ke meja yang sudah dibayar/selesai.");
            }

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

            // Jika status masih kosong, buat pesanan baru
            if ($meja->status === 'kosong') {
                $pesanan = PesananDinein::create([
                    'meja_id' => $meja->id,
                    'nama_konsumen' => $namaKonsumen,
                    'jumlah_tamu' => $jumlahTamu,
                    'status' => 'menunggu_pembayaran',
                    'dibuka_oleh' => $staffId,
                    'dibuka_pada' => now(),
                ]);

                $meja->update(['status' => 'menunggu_pembayaran']);
            } else {
                // Cari pesanan aktif dan update info konsumen
                $pesanan = PesananDinein::where('meja_id', $meja->id)
                    ->where('status', 'menunggu_pembayaran')
                    ->latest()
                    ->first();
                
                if ($pesanan) {
                    $pesanan->update([
                        'nama_konsumen' => $namaKonsumen,
                        'jumlah_tamu' => $jumlahTamu,
                    ]);
                }
            }

            // Tambahkan item
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

            // --- SYNC KE MASTER PESANAN (Global Order List) ---
            $lastPesanan = Pesanan::latest()->first();
            $lastId = $lastPesanan ? $lastPesanan->id : 0;
            $noPesanan = 'INV-' . date('Ymd') . '-' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
            
            $totalHarga = 0;
            foreach ($items as $item) {
                $menu = Menu::find($item['menu_id']);
                $totalHarga += ($menu->harga * $item['qty']);
            }

            $masterPesanan = Pesanan::create([
                'no_pesanan' => $noPesanan,
                'nama_pelanggan' => $namaKonsumen,
                'no_meja' => $meja->nomor_meja,
                'jenis_pesanan' => 'dine_in',
                'tanggal_pesanan' => now(),
                'jumlah_porsi' => collect($items)->sum('qty'),
                'status_pesanan' => 'baru',
                'user_id' => $staffId,
                'total_harga' => $totalHarga,
                'keterangan' => 'Order_ID_Dinein:' . $pesanan->id // Marker untuk koneksi
            ]);

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

            $pesanan->meja->update(['status' => 'terisi']);

            // Update status Master Pesanan
            $masterPesanan = Pesanan::where('keterangan', 'Order_ID_Dinein:' . $pesanan->id)->first();
            if ($masterPesanan) {
                $masterPesanan->update([
                    'status_pembayaran' => 'lunas',
                    'status_pesanan' => 'selesai'
                ]);
            }

            // 3. Potong stok berdasarkan resep menu
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

            // Eksekusi potong stok bahan baku
            foreach ($bahanKebutuhan as $bahanId => $totalKebutuhan) {
                $bahanBaku = BahanBaku::find($bahanId);
                if ($bahanBaku) {
                    $stokAwal = $bahanBaku->stok;
                    
                    // Cek ketersediaan stok
                    if ($stokAwal < $totalKebutuhan) {
                         throw new Exception("Stok untuk bahan baku '{$bahanBaku->nama_bahan}' tidak mencukupi. Sisa stok: {$stokAwal}, Kebutuhan: {$totalKebutuhan}.");
                    }
                    
                    $bahanBaku->stok -= $totalKebutuhan;
                    $bahanBaku->save();

                    // Rekam mutasi stok
                    MutasiStok::create([
                        'bahan_baku_id' => $bahanBaku->id,
                        'jenis' => 'keluar',
                        'jumlah' => $totalKebutuhan,
                        'keterangan' => 'Terjual via POS Dine-In (Order #' . $pesanan->id . ')',
                        'tanggal' => now()->format('Y-m-d'),
                    ]);
                }
            }

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
}
