<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Meja;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DineInPaymentController extends Controller
{
    public function checkout($id)
    {
        // 1. Cari pesanan aktif langsung berdasarkan ID Pesanan atau ID Meja
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja'])
            ->where('id', $id)
            ->where('status_pesanan_id', 1) // 1 = Menunggu Pembayaran
            ->where('jenis_pesanan_id', 1) // Dine in
            ->first();

        if (! $pesanan) {
            $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja'])
                ->where('meja_id', $id)
                ->where('status_pesanan_id', 1)
                ->where('jenis_pesanan_id', 1)
                ->latest()
                ->first();
        }

        if (! $pesanan) {
            return redirect()->route('pos.dinein.index')->with('error', 'Pesanan tidak ditemukan atau tagihan sudah lunas.');
        }

        $meja = $pesanan->meja;
        $totalTagihan = $pesanan->total_tagihan;

        return view('pos.pesanan.checkout', compact('meja', 'pesanan', 'totalTagihan'));
    }

    public function processPayment(Request $request, $mejaId)
    {
        $request->validate([
            'pesanan_id' => 'required|exists:pesanan,id',
            'metode_bayar' => 'required|string', // cash or nontunai
            'total_tagihan' => 'required|numeric',
            'jumlah_bayar' => 'nullable|numeric', // For cash
        ]);

        try {
            DB::beginTransaction();

            $pesanan = Pesanan::findOrFail($request->pesanan_id);
            $metodeId = $request->metode_bayar === 'nontunai' ? 2 : 1; // 2 = QRIS, 1 = Tunai

            // 1. Create pembayaran
            Pembayaran::create([
                'nomor_pembayaran' => 'PAY-'.date('YmdHis').'-'.rand(100, 999),
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran_id' => $metodeId,
                'jenis_pembayaran_id' => 1, // 1 = Pembayaran Penuh
                'jumlah_bayar' => $request->jumlah_bayar ?? $pesanan->total_tagihan,
                'status_pembayaran_id' => 3, // 3 = Lunas
                'diproses_oleh' => Auth::id(),
                'dibayar_pada' => now(),
            ]);

            // 2. Potong stok via OrderService (harus dipanggil sebelum status diupdate jadi selesai)
            app(OrderService::class)->completeOrder($pesanan);

            // 3. Update status pesanan & meja
            $pesanan->update([
                'status_pesanan_id' => 5, // 5 = Selesai
            ]);

            if ($pesanan->meja) {
                $pesanan->meja->update(['status_meja_id' => 1]); // 1 = Tersedia
            }

            DB::commit();

            return redirect()->route('pos.dinein.success', $request->pesanan_id)
                ->with('success', 'Pembayaran berhasil! Silakan cetak nota.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage());
        }
    }

    public function success($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pembayaran'])->findOrFail($pesananId);

        if ($pesanan->status_pesanan_id !== 5) {
            return redirect()->route('pos.dinein.index')->with('error', 'Pesanan belum lunas.');
        }

        return view('pos.pesanan.success', compact('pesanan'));
    }

    public function receipts($pesananId)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'meja', 'pembayaran'])->findOrFail($pesananId);

        return view('pos.dinein.receipts', compact('pesanan'));
    }

    public function chargeQris(Request $request, $pesananId)
    {
        $pesanan = Pesanan::findOrFail($pesananId);

        if ($pesanan->status_pesanan_id !== 1) { // Menunggu Pembayaran
            return redirect()->route('pos.dinein.index')->with('error', 'Pesanan tidak valid untuk pembayaran.');
        }

        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');
        \Midtrans\Config::$isSanitized = config('midtrans.is_sanitized');
        \Midtrans\Config::$is3ds = config('midtrans.is_3ds');

        $orderId = 'QRIS-' . $pesanan->id . '-' . time();
        $grossAmount = (int) $pesanan->total_tagihan;

        $params = [
            'payment_type' => 'gopay',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'gopay' => [
                'enable_callback' => true,
                'callback_url' => route('pos.dinein.index')
            ]
        ];

        try {
            DB::beginTransaction();

            $response = \Midtrans\CoreApi::charge($params);

            if (!isset($response->actions) || empty($response->actions)) {
                throw new \Exception('Gagal mendapatkan QR Code dari Midtrans.');
            }

            // Find QR Code URL
            $qrCodeUrl = '';
            foreach ($response->actions as $action) {
                if ($action->name === 'generate-qr-code') {
                    $qrCodeUrl = $action->url;
                    break;
                }
            }

            if (empty($qrCodeUrl)) {
                throw new \Exception('QR Code URL tidak tersedia dalam response Midtrans.');
            }

            // Save to pembayaran table (status 1 = Menunggu Pembayaran)
            $pembayaran = Pembayaran::create([
                'nomor_pembayaran' => $orderId,
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran_id' => 2, // QRIS
                'jenis_pembayaran_id' => 1,
                'jumlah_bayar' => $grossAmount,
                'status_pembayaran_id' => 1, // Menunggu Pembayaran
                'diproses_oleh' => Auth::id(),
                'midtrans_order_id' => $orderId,
                'midtrans_transaction_id' => $response->transaction_id ?? null,
                'qr_code_url' => $qrCodeUrl,
                'expired_at' => now()->addMinutes(15),
                'response_midtrans' => (array) $response
            ]);

            DB::commit();

            return redirect()->route('pos.dinein.show_qris', $pembayaran->id);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses QRIS: ' . $e->getMessage());
        }
    }

    public function showQris($pembayaranId)
    {
        $pembayaran = Pembayaran::with('pesanan.meja')->findOrFail($pembayaranId);
        
        if ($pembayaran->status_pembayaran_id == 3) {
            return redirect()->route('pos.dinein.success', $pembayaran->pesanan_id);
        }

        return view('pos.pembayaran.qris', compact('pembayaran'));
    }

    public function checkStatus($pembayaranId)
    {
        $pembayaran = Pembayaran::findOrFail($pembayaranId);
        
        return response()->json([
            'status' => $pembayaran->status_pembayaran_id == 3 ? 'success' : 'pending',
            'pesanan_id' => $pembayaran->pesanan_id
        ]);
    }
}
