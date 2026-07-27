<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateQrisPaymentRequest;
use App\Services\MidtransService;
use App\Services\DineInService;
use App\Models\PaymentTransaction;
use App\Models\PesananDinein;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected MidtransService $midtransService;
    protected DineInService $dineInService;

    public function __construct(MidtransService $midtransService, DineInService $dineInService)
    {
        $this->midtransService = $midtransService;
        $this->dineInService = $dineInService;
    }

    /**
     * Endpoint POST /api/payment/qris
     * Generate QRIS Code untuk pembayaran POS Resto
     */
    public function createQris(CreateQrisPaymentRequest $request): JsonResponse
    {
        try {
            $customer = [
                'first_name' => $request->input('customer_name', 'Pelanggan POS'),
                'phone' => $request->input('customer_phone', ''),
            ];

            $result = $this->midtransService->createQrisPayment(
                (int) $request->input('amount'),
                (string) $request->input('din_number'),
                $customer
            );

            return response()->json([
                'success' => true,
                'message' => 'QRIS berhasil dibuat.',
                'data' => $result,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Create QRIS Payment Controller Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Endpoint GET /api/payment/status/{orderId}
     * Cek status transaksi terbaru dari database (menghemat rate limit Midtrans)
     */
    public function checkStatus(string $orderId): JsonResponse
    {
        $transaction = $this->midtransService->checkStatus($orderId);

        if (!$transaction) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan dengan order_id: ' . $orderId,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $transaction->order_id,
                'din_number' => $transaction->din_number,
                'gross_amount' => (int) $transaction->gross_amount,
                'transaction_status' => $transaction->transaction_status,
                'paid_at' => $transaction->paid_at ? $transaction->paid_at->toIso8601String() : null,
                'is_paid' => $transaction->isPaid(),
            ],
        ]);
    }

    /**
     * Endpoint POST /api/payment/notification
     * Webhook Midtrans HTTP Notification Handler
     */
    public function handleNotification(Request $request): JsonResponse
    {
        try {
            $notificationData = $request->all();
            
            $orderId = $notificationData['order_id'] ?? null;
            $statusCode = $notificationData['status_code'] ?? null;
            $grossAmount = $notificationData['gross_amount'] ?? null;
            $reqSignatureKey = $notificationData['signature_key'] ?? null;
            $transactionStatus = $notificationData['transaction_status'] ?? null;
            $fraudStatus = $notificationData['fraud_status'] ?? null;

            if (!$orderId || !$statusCode || !$grossAmount || !$reqSignatureKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payload notifikasi tidak lengkap.',
                ], 400);
            }

            // 1. VERIFIKASI SIGNATURE KEY SHA512
            $serverKey = config('midtrans.server_key');
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

            if ($reqSignatureKey !== $expectedSignature) {
                Log::warning('Midtrans Webhook Invalid Signature Key', [
                    'order_id' => $orderId,
                    'given' => $reqSignatureKey,
                    'expected' => $expectedSignature,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid signature key.',
                ], 403);
            }

            // 2. AMBIL RECORD TRANSAKSI
            $transaction = PaymentTransaction::where('order_id', $orderId)->first();
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ID tidak ditemukan di database local.',
                ], 404);
            }

            // 3. UPDATE STATUS TRANSAKSI DI DATABASE LOCAL
            $isPaid = false;
            if ($transactionStatus == 'capture') {
                if ($fraudStatus == 'challenge') {
                    $transaction->transaction_status = 'challenge';
                } else if ($fraudStatus == 'accept') {
                    $transaction->transaction_status = 'settlement';
                    $isPaid = true;
                }
            } else if ($transactionStatus == 'settlement') {
                $transaction->transaction_status = 'settlement';
                $isPaid = true;
            } else if ($transactionStatus == 'pending') {
                $transaction->transaction_status = 'pending';
            } else if ($transactionStatus == 'deny') {
                $transaction->transaction_status = 'deny';
            } else if ($transactionStatus == 'expire') {
                $transaction->transaction_status = 'expire';
            } else if ($transactionStatus == 'cancel') {
                $transaction->transaction_status = 'cancel';
            }

            if ($isPaid && !$transaction->paid_at) {
                $transaction->paid_at = now();
            }

            $transaction->raw_response = $notificationData;
            $transaction->save();

            // 4. JIKA STATUS SETTLEMENT / LUNAS, OTOMATIS PROSES PESANAN & MEJA RESTO
            if ($isPaid) {
                $dinNumber = $transaction->din_number;
                
                $pesanan = PesananDinein::where('kode_pesanan', $dinNumber)
                    ->orWhere('id', str_replace('DIN-', '', $dinNumber))
                    ->first();

                if ($pesanan && $pesanan->status !== 'lunas') {
                    $this->dineInService->prosesPembayaran(
                        $pesanan->id,
                        'qris',
                        (int) $transaction->gross_amount,
                        1 // Staff / System ID
                    );

                    Log::info("Pesanan Resto #{$dinNumber} (Meja {$pesanan->meja_id}) berhasil dilunasi via Webhook QRIS Midtrans.");
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification processed successfully.',
                'order_id' => $orderId,
                'status' => $transaction->transaction_status,
            ]);

        } catch (\Exception $e) {
            Log::error('Midtrans Notification Handler Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
