<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\DetailPesanan;
use Illuminate\Support\Facades\DB;
use App\Services\StockService;

class OrderService
{
    protected $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Selesaikan pesanan dan potong stok bahan baku sesuai resep menu.
     *
     * @param Pesanan $pesanan
     * @return void
     * @throws \Exception
     */
    public function completeOrder(Pesanan $pesanan)
    {
        if ($pesanan->status_pesanan_id == 5 || $pesanan->status_pesanan_id == 6) {
            throw new \Exception('Pesanan sudah selesai atau dibatalkan.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->load('detail_pesanan.menu.resep_menu.bahan_baku');

            // Potong stok bahan baku untuk tiap detail pesanan
            foreach ($pesanan->detail_pesanan as $detail) {
                $menu = $detail->menu;
                $jumlahDipesan = $detail->jumlah;

                if (!$menu || !$menu->resep_menu) continue;

                foreach ($menu->resep_menu as $resep) {
                    $bahanBakuId = $resep->bahan_baku_id;
                    $kebutuhanPerPorsi = $resep->jumlah_kebutuhan;
                    
                    $totalKebutuhan = $kebutuhanPerPorsi * $jumlahDipesan;

                    $this->stockService->deductStock(
                        $bahanBakuId,
                        $totalKebutuhan,
                        "Pesanan #{$pesanan->nomor_pesanan} (Menu: {$menu->nama_menu})"
                    );
                }
            }

            $pesanan->status_pesanan = 'selesai';
            $pesanan->save();
        });
    }

    /**
     * Batalkan pesanan.
     * Jika sudah terlanjur memotong stok (misal karena logic sebelumnya), bisa direstore di sini.
     * Saat ini asumsikan pemotongan stok hanya terjadi saat pesanan Selesai.
     *
     * @param Pesanan $pesanan
     * @return void
     */
    public function cancelOrder(Pesanan $pesanan)
    {
        if ($pesanan->status_pesanan == 'selesai') {
            throw new \Exception('Pesanan yang sudah selesai tidak bisa dibatalkan secara langsung.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->status_pesanan = 'dibatalkan';
            $pesanan->save();
        });
    }
}
