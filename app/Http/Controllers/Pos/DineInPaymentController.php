<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meja;
use App\Models\PesananDinein;
use App\Services\DineInService;

class DineInPaymentController extends Controller
{
    protected $dineInService;

    public function __construct(DineInService $dineInService)
    {
        $this->dineInService = $dineInService;
        // Hanya kasir (dan admin/pemilik) yang boleh mengakses checkout
        // (asumsi middleware ditambahkan di route, tidak di konstruktor)
    }

    public function checkout($id)
    {
        // 1. Cari pesanan aktif langsung berdasarkan ID Pesanan atau ID Meja
        $pesanan = PesananDinein::with(['items.menu', 'meja'])
            ->where('id', $id)
            ->where('status', 'menunggu_pembayaran')
            ->first();

        if (!$pesanan) {
            $pesanan = PesananDinein::with(['items.menu', 'meja'])
                ->where('meja_id', $id)
                ->where('status', 'menunggu_pembayaran')
                ->latest()
                ->first();
        }

        if (!$pesanan) {
            return redirect()->route('pos.dinein.index')->with('error', 'Pesanan tidak ditemukan atau tagihan sudah lunas.');
        }

        $meja = $pesanan->meja;

        // Kalkulasi Total Tagihan (Aman terhadap null menu)
        $totalTagihan = 0;
        foreach ($pesanan->items as $item) {
            $hargaSatuan = $item->menu->harga ?? $item->harga_satuan ?? 0;
            $totalTagihan += ($item->qty * $hargaSatuan);
        }

        if (!$pesanan->snap_token && $totalTagihan > 0) {
            try {
                \App\Http\Controllers\MidtransController::generateSnapToken($pesanan);
            } catch (\Exception $e) {
                // Abaikan kesalahan Midtrans jika offline agar pembayaran Tunai tetap bisa dilakukan kasir
            }
        }

        return view('pos.dinein.checkout', compact('meja', 'pesanan', 'totalTagihan'));
    }

    public function processPayment(Request $request, $mejaId)
    {

        $request->validate([
            'pesanan_id' => 'required|exists:pesanan_dineins,id',
            'metode_bayar' => 'required|in:cash,qris,kartu,nontunai,promo',
            'total_tagihan' => 'required|numeric'
        ]);

        try {
            $this->dineInService->prosesPembayaran(
                $request->pesanan_id,
                $request->metode_bayar,
                $request->total_tagihan,
                auth()->id()
            );

            if ($request->metode_bayar == 'cash') {
                return redirect()->route('pos.dinein.index')
                                 ->with('print_nota_id', $request->pesanan_id)
                                 ->with('success', 'Pembayaran tunai berhasil! Silakan cetak nota.');
            }

            // Jika qris/kartu dari Midtrans Popup berhasil:
            return redirect()->route('pos.dinein.index')
                             ->with('print_nota_id', $request->pesanan_id)
                             ->with('success', 'Pembayaran non-tunai berhasil diverifikasi! Silakan cetak nota.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function receipts($pesananId)
    {
        $pesanan = PesananDinein::with('items.menu', 'meja', 'pembayaran')->findOrFail($pesananId);
        return view('pos.dinein.receipts', compact('pesanan'));
    }
}
