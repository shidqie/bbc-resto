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
        if ($pesanan->status_pesanan == 'selesai' || $pesanan->status_pesanan == 'dibatalkan') {
            throw new \Exception('Pesanan sudah selesai atau dibatalkan.');
        }

        DB::transaction(function () use ($pesanan) {
            $pesanan->load('details.menu.resep.bahanBaku');

            // Potong stok bahan baku untuk tiap detail pesanan
            foreach ($pesanan->details as $detail) {
                $menu = $detail->menu;
                $jumlahDipesan = $detail->jumlah;

                foreach ($menu->resep as $resep) {
                    $bahanBakuId = $resep->bahan_baku_id;
                    $kebutuhanPerPorsi = $resep->jumlah_kebutuhan;
                    
                    $totalKebutuhan = $kebutuhanPerPorsi * $jumlahDipesan;

                    $this->stockService->deductStock(
                        $bahanBakuId,
                        $totalKebutuhan,
                        "Pesanan #{$pesanan->no_pesanan} (Menu: {$menu->nama})"
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
