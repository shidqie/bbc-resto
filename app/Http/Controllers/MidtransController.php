<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\PesananCatering;
use App\Models\PesananNasiBox;
use App\Models\PesananDinein;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    public static function generateSnapToken($order, $type = 'catering')
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $gross_amount = 0;
        $order_id_prefix = $order->kode_pesanan;

        if ($order instanceof \App\Models\PesananDinein) {
            // Untuk Dine-In, hitung total tagihan dari item
            foreach ($order->items as $item) {
                $gross_amount += ($item->qty * $item->menu->harga);
            }
            $firstName = $order->nama_konsumen ?? 'Pelanggan Dine-In';
            $phone = '080000000000'; // Dummy phone since dine-in might not have it
        } else {
            // We use dp_amount if status is menunggu_dp, else total_tagihan if lunas/etc.
            $gross_amount = $order->dp_amount;
            if($order->status == 'menunggu_pelunasan' || $order->status == 'terkonfirmasi') {
                 // For simplicity, we just charge dp_amount. 
                 // In a full implementation, pelunasan logic handles the rest.
            }
            $firstName = $order->nama_pemesan;
            $phone = $order->kontak;
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order_id_prefix . '-' . time(), // append time to avoid duplicate order id on midtrans if retried
                'gross_amount' => $gross_amount,
            ],
            'customer_details' => [
                'first_name' => $firstName,
                'phone' => $phone,
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $order->update(['snap_token' => $snapToken]);
            return $snapToken;
        } catch (\Exception $e) {
            Log::error("Midtrans Snap Error: " . $e->getMessage());
            return null;
        }
    }

    public function notificationCallback(Request $request)
    {
        $payload = $request->getContent();
        $notification = json_decode($payload);

        $validSignatureKey = hash("sha512", $notification->order_id . $notification->status_code . $notification->gross_amount . config('midtrans.server_key'));

        if ($notification->signature_key != $validSignatureKey) {
            return response(['message' => 'Invalid signature'], 403);
        }

        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $orderIdArray = explode('-', $notification->order_id);
        $orderId = $orderIdArray[0];
        $fraud = $notification->fraud_status;

        $order = null;
        if (strpos($orderId, 'CTR') === 0) {
            $order = PesananCatering::where('kode_pesanan', $orderId)->first();
        } else if (strpos($orderId, 'NBX') === 0) {
            $order = PesananNasiBox::where('kode_pesanan', $orderId)->first();
        } else if (strpos($orderId, 'DIN') === 0) {
            $order = PesananDinein::with('items.menu')->where('kode_pesanan', $orderId)->first();
        }

        if (!$order) {
            return response(['message' => 'Order not found'], 404);
        }

        if (($transaction == 'capture' && $type == 'credit_card' && $fraud != 'challenge') || $transaction == 'settlement') {
            // Check if already confirmed to prevent double processing
            if ($order->status !== 'terkonfirmasi' && $order->status !== 'diproses' && $order->status !== 'dikirim' && $order->status !== 'selesai' && $order->status !== 'lunas') {
                $order->update(['status' => 'terkonfirmasi']);
                
                // Deduct stock if Catering
                if ($order instanceof \App\Models\PesananCatering) {
                    \App\Services\PesananCateringService::potongStok($order);
                } else if ($order instanceof \App\Models\PesananNasiBox) {
                    \App\Services\PesananNasiBoxService::potongStok($order);
                } else if ($order instanceof \App\Models\PesananDinein) {
                    // Stok sudah dipotong saat input order, cukup catat pembayaran
                    $totalHarga = 0;
                    foreach ($order->items as $item) {
                        $totalHarga += ($item->qty * $item->menu->harga);
                    }
                    
                    app(\App\Services\DineInService::class)->prosesPembayaran(
                        $order->id,
                        'qris', // Default to qris or get from midtrans
                        $totalHarga,
                        $order->dibuka_oleh
                    );
                }

                // Send Email Notification
                if ($order->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($order->email)->send(new \App\Mail\PaymentReceiptMail($order));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Midtrans Webhook Email Error: ' . $e->getMessage());
                    }
                }
            }
        } else if ($transaction == 'pending') {
            // $order->update(['status' => 'menunggu_dp']);
        } else if ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            $order->update(['status' => 'dibatalkan']);
        }

        return response(['message' => 'Success']);
    }
}
