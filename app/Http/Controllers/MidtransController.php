<?php

namespace App\Http\Controllers;

use App\Models\PaymentTransaction;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;
use Exception;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate Midtrans Snap Token (digunakan oleh halaman pembayaran).
     * Enhanced with comprehensive error handling and logging
     * Nominal dihitung dinamis: DP (uang muka) atau Pelunasan (sisa tagihan).
     * 
     * Validates: Requirements 1.1-1.4, 6.1-6.4
     */
    public function getSnapToken(Request $request)
    {
        $tokenId = uniqid('token_', true);
        
        Log::info("[Token:{$tokenId}] Snap token generation initiated", [
            'request_data' => $request->only(['kode_pesanan'])
        ]);

        try {
            $request->validate(['kode_pesanan' => 'required|string']);

            $pesanan = Pesanan::where('nomor_pesanan', $request->kode_pesanan)->first();
            if (!$pesanan) {
                Log::warning("[Token:{$tokenId}] Order not found", [
                    'kode_pesanan' => $request->kode_pesanan
                ]);
                return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
            }

            Log::info("[Token:{$tokenId}] Order found", [
                'pesanan_id' => $pesanan->id,
                'total_tagihan' => $pesanan->total_tagihan
            ]);

            // Calculate payment amounts
            $dpTerbayar = (float) $pesanan->pembayaran()->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
            $lunas = (float) $pesanan->pembayaran()->where('status_pembayaran_id', 3)->sum('jumlah_bayar');

            $isPelunasan = $lunas >= $pesanan->nominalDP() && $lunas < (float) $pesanan->total_tagihan;
            $amount = $isPelunasan
                ? max(0, (float) $pesanan->total_tagihan - $lunas)
                : max(0, $pesanan->nominalDP() - $dpTerbayar);

            Log::info("[Token:{$tokenId}] Payment calculation", [
                'dp_terbayar' => $dpTerbayar,
                'lunas' => $lunas,
                'is_pelunasan' => $isPelunasan,
                'calculated_amount' => $amount,
                'nominal_dp' => $pesanan->nominalDP()
            ]);

            if ($amount <= 0) {
                Log::warning("[Token:{$tokenId}] No payment required", [
                    'amount' => $amount,
                    'total_tagihan' => $pesanan->total_tagihan,
                    'lunas' => $lunas
                ]);
                return response()->json(['success' => false, 'message' => 'Tagihan sudah lunas.'], 422);
            }

            // Generate unique order_id
            $orderId = $pesanan->nomor_pesanan . ($isPelunasan ? '-LNS-' : '-DP-') . time();

            // Prepare customer details
            $customerName = optional($pesanan->pelanggan)->nama ?? 'Pelanggan';
            $customerPhone = optional($pesanan->pelanggan)->no_telepon ?? '';

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => (int) $amount,
                ],
                'customer_details' => [
                    'first_name' => $customerName,
                    'phone' => $customerPhone,
                ],
                'item_details' => [
                    [
                        'id' => $pesanan->nomor_pesanan,
                        'price' => (int) $amount,
                        'quantity' => 1,
                        'name' => ($isPelunasan ? 'Pelunasan' : 'Uang Muka') . ' - ' . $pesanan->nomor_pesanan
                    ]
                ],
                'callbacks' => [
                    'finish' => url("/pesan/bayar/{$request->kode_pesanan}")
                ]
            ];

            Log::info("[Token:{$tokenId}] Requesting Snap token", [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
                'customer' => $customerName
            ]);

            try {
                $snapToken = Snap::getSnapToken($params);
                
                Log::info("[Token:{$tokenId}] Snap token generated successfully", [
                    'order_id' => $orderId,
                    'token_length' => strlen($snapToken)
                ]);

            } catch (\Midtrans\Exceptions\MidtransException $e) {
                Log::error("[Token:{$tokenId}] Midtrans API error", [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'order_id' => $orderId,
                    'amount' => $amount
                ]);

                return response()->json([
                    'success' => false, 
                    'message' => 'Gagal menyiapkan pembayaran: ' . $e->getMessage()
                ], 500);

            } catch (Exception $e) {
                Log::error("[Token:{$tokenId}] Unexpected error during Snap token generation", [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);

                return response()->json([
                    'success' => false, 
                    'message' => 'Gagal menyiapkan pembayaran: ' . $e->getMessage()
                ], 500);
            }

            // Create payment transaction record
            try {
                PaymentTransaction::create([
                    'order_id' => $orderId,
                    'din_number' => $pesanan->nomor_pesanan,
                    'gross_amount' => (int) $amount,
                    'payment_type' => 'snap',
                    'transaction_status' => 'pending',
                    'raw_response' => ['snap_token' => $snapToken, 'generated_at' => now()],
                    'signature_verified' => false, // Will be updated when webhook is received
                    'retry_count' => 0
                ]);

                Log::info("[Token:{$tokenId}] Payment transaction record created", [
                    'order_id' => $orderId
                ]);

            } catch (Exception $e) {
                Log::error("[Token:{$tokenId}] Failed to create payment transaction record", [
                    'error' => $e->getMessage(),
                    'order_id' => $orderId
                ]);

                // Continue anyway since Snap token was generated successfully
            }

            return response()->json([
                'success' => true, 
                'snap_token' => $snapToken, 
                'order_id' => $orderId,
                'amount' => $amount,
                'is_pelunasan' => $isPelunasan
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning("[Token:{$tokenId}] Validation failed", [
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Data tidak valid: ' . implode(', ', array_flatten($e->errors()))
            ], 422);

        } catch (Exception $e) {
            Log::error("[Token:{$tokenId}] Snap token generation failed", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'kode_pesanan' => $request->kode_pesanan ?? 'unknown'
            ]);

            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem saat menyiapkan pembayaran.'
            ], 500);
        }
    }

    /**
     * Webhook Callback dari Midtrans (Main Flow Step 7 & 8)
     * Enhanced with proper SHA512 signature verification and comprehensive error handling
     * 
     * Validates: Requirements 1.5, 1.6, 6.1-6.4, 7.1
     */
    public function notificationCallback(Request $request)
    {
        $startTime = microtime(true);
        $webhookId = uniqid('webhook_', true);
        
        Log::info("[Webhook:{$webhookId}] Midtrans webhook received", [
            'headers' => $request->headers->all(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip()
        ]);

        try {
            // Get raw payload and decode
            $payload = $request->getContent();
            
            if (empty($payload)) {
                Log::warning("[Webhook:{$webhookId}] Empty webhook payload received");
                return $this->webhookResponse('Empty payload', 400);
            }

            $notification = json_decode($payload, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("[Webhook:{$webhookId}] Invalid JSON payload", [
                    'json_error' => json_last_error_msg(),
                    'payload_length' => strlen($payload)
                ]);
                return $this->webhookResponse('Invalid JSON payload', 400);
            }

            Log::info("[Webhook:{$webhookId}] Webhook payload decoded", [
                'order_id' => $notification['order_id'] ?? 'missing',
                'transaction_status' => $notification['transaction_status'] ?? 'missing',
                'payment_type' => $notification['payment_type'] ?? 'missing'
            ]);

            // Validate required fields
            $requiredFields = ['order_id', 'status_code', 'gross_amount', 'signature_key', 'transaction_status'];
            $missingFields = [];
            
            foreach ($requiredFields as $field) {
                if (!isset($notification[$field]) || $notification[$field] === '') {
                    $missingFields[] = $field;
                }
            }
            
            if (!empty($missingFields)) {
                Log::error("[Webhook:{$webhookId}] Missing required fields", [
                    'missing_fields' => $missingFields,
                    'received_keys' => array_keys($notification)
                ]);
                return $this->webhookResponse('Missing required fields: ' . implode(', ', $missingFields), 400);
            }

            // Record webhook receipt immediately
            $webhookReceivedAt = now();
            
            // Update existing transaction with webhook receipt time
            PaymentTransaction::where('order_id', $notification['order_id'])
                ->update(['webhook_received_at' => $webhookReceivedAt]);

            // Verify signature using proper SHA512 method
            if (!$this->verifySignature($notification)) {
                Log::error("[Webhook:{$webhookId}] Invalid webhook signature", [
                    'order_id' => $notification['order_id'],
                    'provided_signature' => $notification['signature_key']
                ]);
                
                // Update transaction with failed verification
                PaymentTransaction::where('order_id', $notification['order_id'])
                    ->update([
                        'signature_verified' => false,
                        'raw_response' => array_merge(
                            PaymentTransaction::where('order_id', $notification['order_id'])->value('raw_response') ?? [],
                            ['webhook_signature_failed' => $notification]
                        )
                    ]);
                
                return $this->webhookResponse('Invalid signature', 403);
            }

            Log::info("[Webhook:{$webhookId}] Signature verified successfully", [
                'order_id' => $notification['order_id']
            ]);

            // Update transaction with successful verification
            PaymentTransaction::where('order_id', $notification['order_id'])
                ->update(['signature_verified' => true]);

            // Extract order information
            $orderIdFull = $notification['order_id'];
            $transaction = $notification['transaction_status'];
            $paymentType = $notification['payment_type'];
            $fraud = $notification['fraud_status'] ?? null;
            
            // Extract kode pesanan from order_id
            $kodePesanan = $this->extractKodePesananFromOrderId($orderIdFull);
            
            if (!$kodePesanan) {
                Log::error("[Webhook:{$webhookId}] Could not extract kode pesanan from order_id", [
                    'order_id' => $orderIdFull
                ]);
                return $this->webhookResponse('Invalid order_id format', 400);
            }

            // Find the pesanan
            $pesanan = Pesanan::where('nomor_pesanan', $kodePesanan)->first();

            if (!$pesanan) {
                Log::error("[Webhook:{$webhookId}] Order not found", [
                    'kode_pesanan' => $kodePesanan,
                    'order_id' => $orderIdFull
                ]);
                
                // Update transaction status even if order not found
                PaymentTransaction::where('order_id', $orderIdFull)
                    ->update([
                        'transaction_status' => $transaction,
                        'raw_response' => array_merge(
                            PaymentTransaction::where('order_id', $orderIdFull)->value('raw_response') ?? [],
                            ['webhook_order_not_found' => $notification]
                        )
                    ]);
                
                return $this->webhookResponse('Order not found', 404);
            }

            Log::info("[Webhook:{$webhookId}] Processing payment status", [
                'kode_pesanan' => $kodePesanan,
                'transaction_status' => $transaction,
                'payment_type' => $paymentType,
                'fraud_status' => $fraud
            ]);

            // Process payment based on transaction status
            $processed = false;
            
            if (($transaction == 'capture' && $paymentType == 'credit_card' && $fraud != 'challenge') || $transaction == 'settlement') {
                // Successful payment - use idempotent processing
                $processed = $this->processSuccessPayment(
                    $pesanan, 
                    $orderIdFull, 
                    $paymentType, 
                    (float) $notification['gross_amount'],
                    $webhookId
                );
            } elseif (in_array($transaction, ['deny', 'expire', 'cancel', 'failure'])) {
                // Failed/cancelled payment
                PaymentTransaction::where('order_id', $orderIdFull)->update([
                    'transaction_status' => $transaction,
                    'raw_response' => array_merge(
                        PaymentTransaction::where('order_id', $orderIdFull)->value('raw_response') ?? [],
                        ['webhook_failed' => $notification]
                    )
                ]);
                $processed = true;
                
                Log::info("[Webhook:{$webhookId}] Payment failed/cancelled", [
                    'order_id' => $orderIdFull,
                    'status' => $transaction
                ]);
            } else {
                // Other statuses (pending, etc.)
                PaymentTransaction::where('order_id', $orderIdFull)->update([
                    'transaction_status' => $transaction,
                    'raw_response' => array_merge(
                        PaymentTransaction::where('order_id', $orderIdFull)->value('raw_response') ?? [],
                        ['webhook_status_update' => $notification]
                    )
                ]);
                $processed = true;
                
                Log::info("[Webhook:{$webhookId}] Payment status updated", [
                    'order_id' => $orderIdFull,
                    'status' => $transaction
                ]);
            }

            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::info("[Webhook:{$webhookId}] Webhook processing completed", [
                'success' => $processed,
                'processing_time_ms' => $processingTime,
                'order_id' => $orderIdFull,
                'final_status' => $transaction
            ]);

            return $this->webhookResponse('Success', 200);
            
        } catch (Exception $e) {
            $processingTime = round((microtime(true) - $startTime) * 1000, 2);
            
            Log::error("[Webhook:{$webhookId}] Webhook processing failed", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'processing_time_ms' => $processingTime,
                'payload_sample' => substr($payload ?? '', 0, 200)
            ]);

            // Increment retry count for failed processing
            if (isset($notification['order_id'])) {
                PaymentTransaction::where('order_id', $notification['order_id'])
                    ->increment('retry_count');
            }

            return $this->webhookResponse('Internal server error', 500);
        }
    }

    /**
     * Verify webhook signature using proper SHA512 method according to Midtrans documentation
     * Validates: Requirement 7.1 (Security)
     */
    private function verifySignature(array $notification): bool 
    {
        try {
            $orderId = $notification['order_id'];
            $statusCode = $notification['status_code'];
            $grossAmount = $notification['gross_amount'];
            $serverKey = config('midtrans.server_key');
            $providedSignature = $notification['signature_key'];

            // Create signature string as per Midtrans documentation: order_id+status_code+gross_amount+ServerKey
            $signatureString = $orderId . $statusCode . $grossAmount . $serverKey;
            
            // Generate SHA512 hash
            $calculatedSignature = hash('sha512', $signatureString);

            $isValid = hash_equals($calculatedSignature, $providedSignature);

            Log::debug("Signature verification", [
                'order_id' => $orderId,
                'signature_string_length' => strlen($signatureString),
                'provided_signature' => $providedSignature,
                'calculated_signature' => $calculatedSignature,
                'is_valid' => $isValid
            ]);

            return $isValid;
            
        } catch (Exception $e) {
            Log::error("Signature verification failed", [
                'error' => $e->getMessage(),
                'order_id' => $notification['order_id'] ?? 'unknown'
            ]);
            
            return false;
        }
    }

    /**
     * Extract kode pesanan from Midtrans order_id
     */
    private function extractKodePesananFromOrderId(string $orderIdFull): ?string
    {
        // Handle both DP and Pelunasan formats: KODE-DP-timestamp or KODE-LNS-timestamp
        if (preg_match('/^(.+?)-(DP|LNS)-\d+$/', $orderIdFull, $matches)) {
            return $matches[1];
        }
        
        // Fallback for other formats
        return explode('-', $orderIdFull)[0] ?? null;
    }

    /**
     * Generate standardized webhook response
     */
    private function webhookResponse(string $message, int $statusCode): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'message' => $message,
            'timestamp' => now()->toISOString()
        ], $statusCode);
    }

    /**
     * Proses pembayaran sukses (DP atau Pelunasan) — idempotent per order_id.
     * Enhanced with comprehensive logging and duplicate prevention
     * 
     * Validates: Requirements 1.5, 1.6, 6.1-6.4
     */
    public function processSuccessPayment(
        Pesanan $pesanan, 
        string $orderIdFull = '', 
        string $paymentType = 'qris', 
        float $grossAmount = 0,
        string $webhookId = null
    ): bool {
        $webhookId = $webhookId ?: uniqid('payment_', true);
        
        try {
            Log::info("[Payment:{$webhookId}] Processing successful payment", [
                'order_id' => $orderIdFull,
                'pesanan_id' => $pesanan->id,
                'kode_pesanan' => $pesanan->nomor_pesanan,
                'payment_type' => $paymentType,
                'gross_amount' => $grossAmount
            ]);

            $isPelunasan = strpos($orderIdFull, '-LNS-') !== false;

            // IDEMPOTENCY CHECK: Prevent duplicate payments for same order_id
            $existingPayment = Pembayaran::where('nomor_referensi', $orderIdFull)->first();
            
            if ($existingPayment) {
                Log::warning("[Payment:{$webhookId}] Duplicate payment detected - idempotency protection", [
                    'order_id' => $orderIdFull,
                    'existing_payment_id' => $existingPayment->id,
                    'existing_amount' => $existingPayment->jumlah_bayar,
                    'new_amount' => $grossAmount
                ]);

                // Update transaction as processed to prevent retry
                PaymentTransaction::where('order_id', $orderIdFull)->update([
                    'transaction_status' => 'settlement',
                    'processed_at' => now(),
                    'raw_response' => array_merge(
                        PaymentTransaction::where('order_id', $orderIdFull)->value('raw_response') ?? [],
                        ['duplicate_prevented' => ['timestamp' => now(), 'existing_payment_id' => $existingPayment->id]]
                    )
                ]);

                return true; // Return success since payment already exists
            }

            // Validate payment amount
            if ($grossAmount <= 0) {
                Log::error("[Payment:{$webhookId}] Invalid payment amount", [
                    'gross_amount' => $grossAmount,
                    'order_id' => $orderIdFull
                ]);
                return false;
            }

            // Map payment type to method ID
            $metodeId = $this->mapPaymentTypeToMethodId($paymentType);

            // Process payment in database transaction for atomicity
            $paymentProcessed = DB::transaction(function () use (
                $pesanan, $orderIdFull, $isPelunasan, $metodeId, $grossAmount, $paymentType, $webhookId
            ) {
                // Create payment record
                $pembayaran = Pembayaran::create([
                    'nomor_pembayaran' => 'PAY-' . date('YmdHis') . '-' . rand(100, 999),
                    'pesanan_id' => $pesanan->id,
                    'metode_pembayaran_id' => $metodeId,
                    'status_pembayaran_id' => 3, // LUNAS
                    'jenis_pembayaran_id' => $isPelunasan ? 3 : 2, // PELUNASAN / UANG_MUKA
                    'jumlah_bayar' => $grossAmount,
                    'nomor_referensi' => $orderIdFull,
                    'dibayar_pada' => now(),
                    'catatan' => 'Pembayaran otomatis via Midtrans (' . ($isPelunasan ? 'Pelunasan' : 'Uang Muka') . ')',
                    'auto_verified' => true,
                    'webhook_data' => [
                        'payment_type' => $paymentType,
                        'processed_by_webhook' => $webhookId,
                        'processed_at' => now()
                    ]
                ]);

                Log::info("[Payment:{$webhookId}] Payment record created", [
                    'payment_id' => $pembayaran->id,
                    'nomor_pembayaran' => $pembayaran->nomor_pembayaran,
                    'amount' => $grossAmount,
                    'is_pelunasan' => $isPelunasan
                ]);

                // Update order status if needed (only if not already completed/cancelled)
                if (!in_array($pesanan->status_pesanan_id, [5, 6])) {
                    $oldStatus = $pesanan->status_pesanan_id;
                    $pesanan->update(['status_pesanan_id' => 2]); // DIKONFIRMASI
                    
                    Log::info("[Payment:{$webhookId}] Order status updated", [
                        'pesanan_id' => $pesanan->id,
                        'old_status' => $oldStatus,
                        'new_status' => 2
                    ]);
                }

                // Update payment transaction record
                PaymentTransaction::where('order_id', $orderIdFull)->update([
                    'transaction_status' => 'settlement',
                    'payment_type' => $paymentType,
                    'gross_amount' => (int) $grossAmount,
                    'processed_at' => now(),
                    'raw_response' => array_merge(
                        PaymentTransaction::where('order_id', $orderIdFull)->value('raw_response') ?? [],
                        [
                            'payment_processed' => [
                                'payment_id' => $pembayaran->id,
                                'processed_at' => now(),
                                'webhook_id' => $webhookId
                            ]
                        ]
                    )
                ]);

                return $pembayaran->id;
            });

            if ($paymentProcessed) {
                Log::info("[Payment:{$webhookId}] Payment processing completed successfully", [
                    'payment_id' => $paymentProcessed,
                    'order_id' => $orderIdFull,
                    'pesanan_id' => $pesanan->id
                ]);
                return true;
            }

            return false;

        } catch (Exception $e) {
            Log::error("[Payment:{$webhookId}] Payment processing failed", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'order_id' => $orderIdFull,
                'pesanan_id' => $pesanan->id
            ]);

            // Increment retry count on failure
            PaymentTransaction::where('order_id', $orderIdFull)->increment('retry_count');

            return false;
        }
    }

    /**
     * Map Midtrans payment type to internal payment method ID
     */
    private function mapPaymentTypeToMethodId(string $paymentType): int
    {
        return match ($paymentType) {
            'bank_transfer', 'transfer', 'echannel', 'bca_va', 'bni_va', 'bri_va', 'permata_va' => 3, // Bank Transfer/VA
            'credit_card' => 4, // Credit Card
            'gopay', 'shopeepay', 'dana' => 5, // E-Wallet (if exists)
            'qris', 'gopay_qris' => 2, // QRIS
            default => 2, // Default to QRIS
        };
    }

    /**
     * Alternate Flow 6a: Pengecekan status manual ke API Payment Gateway (Polling Fallback)
     * Enhanced with better error handling and logging
     * 
     * Validates: Requirements 1.4, 6.1-6.4
     */
    public function checkStatusManual($kodePesanan)
    {
        $checkId = uniqid('status_check_', true);
        
        Log::info("[StatusCheck:{$checkId}] Manual status check initiated", [
            'kode_pesanan' => $kodePesanan
        ]);

        try {
            $pesanan = Pesanan::where('nomor_pesanan', $kodePesanan)->first();

            if (!$pesanan) {
                Log::warning("[StatusCheck:{$checkId}] Order not found", [
                    'kode_pesanan' => $kodePesanan
                ]);
                return back()->with('error', 'Pesanan tidak ditemukan.');
            }

            // Simulasikan pengecekan jika di local environment
            if (config('app.env') === 'local') {
                Log::info("[StatusCheck:{$checkId}] Using local simulation mode");
                
                $transaksi = PaymentTransaction::where('din_number', $kodePesanan)
                    ->where('transaction_status', '!=', 'settlement')
                    ->latest()
                    ->first();

                if ($transaksi) {
                    Log::info("[StatusCheck:{$checkId}] Found pending transaction for simulation", [
                        'transaction_id' => $transaksi->id,
                        'order_id' => $transaksi->order_id,
                        'current_status' => $transaksi->transaction_status
                    ]);

                    $processed = $this->processSuccessPayment(
                        $pesanan, 
                        $transaksi->order_id, 
                        $transaksi->payment_type ?: 'qris', 
                        (float) $transaksi->gross_amount,
                        $checkId
                    );

                    if ($processed) {
                        Log::info("[StatusCheck:{$checkId}] Local simulation successful");
                        return redirect()->route('pesanan.bayar', $kodePesanan)
                            ->with('success', 'Status pembayaran berhasil diverifikasi! Pesanan kini terkonfirmasi.');
                    } else {
                        Log::error("[StatusCheck:{$checkId}] Local simulation failed");
                        return redirect()->route('pesanan.bayar', $kodePesanan)
                            ->with('error', 'Gagal memverifikasi status pembayaran.');
                    }
                } else {
                    Log::info("[StatusCheck:{$checkId}] No pending transactions found");
                    return redirect()->route('pesanan.bayar', $kodePesanan)
                        ->with('info', 'Tidak ada transaksi yang perlu diverifikasi.');
                }
            }

            // Production: Check with actual Midtrans API
            Log::info("[StatusCheck:{$checkId}] Checking with Midtrans API");
            
            try {
                $midtransStatus = \Midtrans\Transaction::status($kodePesanan);
                $transaction = $midtransStatus->transaction_status ?? '';
                $orderId = $midtransStatus->order_id ?? $kodePesanan;

                Log::info("[StatusCheck:{$checkId}] Midtrans API response", [
                    'transaction_status' => $transaction,
                    'order_id' => $orderId,
                    'payment_type' => $midtransStatus->payment_type ?? 'unknown'
                ]);

                if (in_array($transaction, ['settlement', 'capture'])) {
                    // Find the corresponding order_id with timestamp suffix
                    $paymentTransaction = PaymentTransaction::where('din_number', $kodePesanan)
                        ->where('transaction_status', '!=', 'settlement')
                        ->latest()
                        ->first();

                    $orderIdToProcess = $paymentTransaction ? $paymentTransaction->order_id : $orderId;
                    
                    $processed = $this->processSuccessPayment(
                        $pesanan, 
                        $orderIdToProcess,
                        $midtransStatus->payment_type ?? 'qris',
                        (float) ($midtransStatus->gross_amount ?? 0),
                        $checkId
                    );

                    if ($processed) {
                        Log::info("[StatusCheck:{$checkId}] Manual verification successful");
                        return redirect()->route('pesanan.bayar', $kodePesanan)
                            ->with('success', 'Status transaksi diverifikasi otomatis: LUNAS!');
                    } else {
                        Log::error("[StatusCheck:{$checkId}] Manual verification failed");
                        return redirect()->route('pesanan.bayar', $kodePesanan)
                            ->with('error', 'Gagal memproses pembayaran yang sudah lunas.');
                    }
                } elseif (in_array($transaction, ['deny', 'cancel', 'expire'])) {
                    Log::info("[StatusCheck:{$checkId}] Payment failed or expired", [
                        'status' => $transaction
                    ]);
                    
                    // Update transaction status
                    PaymentTransaction::where('din_number', $kodePesanan)->update([
                        'transaction_status' => $transaction
                    ]);

                    return redirect()->route('pesanan.bayar', $kodePesanan)
                        ->with('error', 'Pembayaran gagal atau expired. Silakan coba bayar ulang sisa tagihan.');
                } else {
                    Log::info("[StatusCheck:{$checkId}] Payment still pending", [
                        'status' => $transaction
                    ]);
                    
                    return redirect()->route('pesanan.bayar', $kodePesanan)
                        ->with('info', 'Status pembayaran saat ini: ' . strtoupper($transaction));
                }
                
            } catch (\Midtrans\Exceptions\MidtransException $e) {
                Log::error("[StatusCheck:{$checkId}] Midtrans API error", [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);

                return redirect()->route('pesanan.bayar', $kodePesanan)
                    ->with('error', 'Gagal memeriksa status ke Payment Gateway: ' . $e->getMessage());
            }

        } catch (Exception $e) {
            Log::error("[StatusCheck:{$checkId}] Manual status check failed", [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'kode_pesanan' => $kodePesanan
            ]);

            return redirect()->route('pesanan.bayar', $kodePesanan)
                ->with('error', 'Terjadi kesalahan saat memeriksa status pembayaran.');
        }
    }
}
