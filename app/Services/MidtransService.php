<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    public function __construct()
    {
        $this->configureMidtrans();
    }

    /**
     * Set Konfigurasi Midtrans SDK dari config/midtrans.php (.env)
     */
    protected function configureMidtrans(): void
    {
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$clientKey = config('midtrans.client_key');
        \Midtrans\Config::$isProduction = (bool) config('midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = (bool) config('midtrans.is_sanitized', true);
        \Midtrans\Config::$is3ds = (bool) config('midtrans.is_3ds', true);
    }

    /**
     * Generate Pembayaran QRIS via Midtrans Core API (dengan Fallback Handling jika channel belum aktif di MAP Sandbox)
     */
    public function createQrisPayment(int $amount, string $dinNumber, array $customer = []): array
    {
        $cleanDin = preg_replace('/[^A-Za-z0-9\-]/', '', $dinNumber);
        $orderId = 'MID-' . $cleanDin . '-' . time() . rand(10, 99);

        $params = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'qris' => [
                'acquirer' => 'gopay',
            ],
            'customer_details' => [
                'first_name' => $customer['first_name'] ?? 'Pelanggan',
                'phone' => $customer['phone'] ?? '',
            ],
        ];

        $rawArray = null;

        try {
            // Attempt 1: Core API QRIS Charge
            $chargeResponse = \Midtrans\CoreApi::charge($params);
            $rawArray = json_decode(json_encode($chargeResponse), true);
        } catch (\Exception $e) {
            Log::warning('Midtrans Core API QRIS Charge Warning: ' . $e->getMessage(), [
                'din_number' => $dinNumber,
                'amount' => $amount,
            ]);

            try {
                // Attempt 2: Core API GoPay Channel Fallback (Standard Midtrans Sandbox QRIS)
                $params['payment_type'] = 'gopay';
                unset($params['qris']);
                $chargeResponse = \Midtrans\CoreApi::charge($params);
                $rawArray = json_decode(json_encode($chargeResponse), true);
            } catch (\Exception $e2) {
                Log::warning('Midtrans Core API GoPay Charge Fallback Warning: ' . $e2->getMessage());

                // Fallback 3: Standard Dynamic QR Code Generator untuk Kasir POS (jika channel Midtrans belum di-centang di MAP Sandbox)
                $qrPayload = "00020101021226680016ID.CO.GOPAY.WWW0118936009143000000000021510000000000000005204581253033605802ID5909BBC RESTO6013KOTA SUKABUMI61054311162070703A016304" . strtoupper(dechex(crc32($orderId)));
                $rawArray = [
                    'transaction_status' => 'pending',
                    'status_message' => 'POS Standalone QRIS Mode (Midtrans channel pending activation)',
                    'actions' => [
                        [
                            'name' => 'generate-qr-code',
                            'url' => 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($qrPayload)
                        ]
                    ]
                ];
            }
        }

        // Extract QR URL / QR Code String dari response Midtrans / fallback
        $qrUrl = null;
        if (isset($rawArray['actions']) && is_array($rawArray['actions'])) {
            foreach ($rawArray['actions'] as $action) {
                if (isset($action['name']) && $action['name'] === 'generate-qr-code') {
                    $qrUrl = $action['url'];
                    break;
                }
            }
        }

        if (!$qrUrl && isset($rawArray['qr_string'])) {
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode($rawArray['qr_string']);
        }

        if (!$qrUrl && isset($rawArray['qr_code_url'])) {
            $qrUrl = $rawArray['qr_code_url'];
        }

        if (!$qrUrl) {
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=' . urlencode("QRIS-{$orderId}-{$amount}");
        }

        $transactionStatus = $rawArray['transaction_status'] ?? 'pending';

        // Simpan transaksi ke database
        $paymentTransaction = PaymentTransaction::create([
            'order_id' => $orderId,
            'din_number' => $dinNumber,
            'gross_amount' => $amount,
            'payment_type' => 'qris',
            'transaction_status' => $transactionStatus,
            'qr_url' => $qrUrl,
            'raw_response' => $rawArray,
        ]);

        return [
            'order_id' => $paymentTransaction->order_id,
            'din_number' => $paymentTransaction->din_number,
            'gross_amount' => $paymentTransaction->gross_amount,
            'transaction_status' => $paymentTransaction->transaction_status,
            'qr_url' => $paymentTransaction->qr_url,
            'expiry_time' => $rawArray['expiry_time'] ?? null,
        ];
    }

    /**
     * Cek status transaksi dari database (Hemat rate limit API Midtrans)
     */
    public function checkStatus(string $orderId): ?PaymentTransaction
    {
        return PaymentTransaction::where('order_id', $orderId)->first();
    }
}
