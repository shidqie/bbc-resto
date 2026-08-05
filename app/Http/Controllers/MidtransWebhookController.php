<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans Webhook Received: ', $payload);

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (!$orderId || !$transactionStatus) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $pembayaran = Pembayaran::where('midtrans_order_id', $orderId)->first();

        if (!$pembayaran) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        try {
            DB::beginTransaction();

            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $pembayaran->update(['status_pembayaran_id' => 1]); // Menunggu
                } else if ($fraudStatus == 'accept') {
                    $this->processSuccess($pembayaran);
                }
            } else if ($transactionStatus == 'settlement') {
                $this->processSuccess($pembayaran);
            } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
                $pembayaran->update(['status_pembayaran_id' => 4]); // Gagal / Batal
            } else if ($transactionStatus == 'pending') {
                $pembayaran->update(['status_pembayaran_id' => 1]);
            }

            DB::commit();
            return response()->json(['message' => 'OK']);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['message' => 'Error processing webhook'], 500);
        }
    }

    private function processSuccess(Pembayaran $pembayaran)
    {
        // Check if already processed
        if ($pembayaran->status_pembayaran_id == 3) {
            return;
        }

        $pembayaran->update([
            'status_pembayaran_id' => 3, // Lunas
            'dibayar_pada' => now()
        ]);

        $pesanan = $pembayaran->pesanan;
        if ($pesanan) {
            // Potong stok via OrderService
            app(OrderService::class)->completeOrder($pesanan);

            $pesanan->update(['status_pesanan_id' => 5]); // Selesai
            
            if ($pesanan->meja) {
                $pesanan->meja->update(['status_meja_id' => 1]); // Tersedia
            }
        }
    }
}
