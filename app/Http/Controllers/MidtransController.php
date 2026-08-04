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
     * Nominal dihitung dinamis: DP (uang muka) atau Pelunasan (sisa tagihan).
     */
    public function getSnapToken(Request $request)
    {
        $request->validate(['kode_pesanan' => 'required|string']);

        $pesanan = Pesanan::where('nomor_pesanan', $request->kode_pesanan)->first();
        if (! $pesanan) {
            return response()->json(['success' => false, 'message' => 'Pesanan tidak ditemukan.'], 404);
        }

        $dpTerbayar = (float) $pesanan->pembayaran()->whereIn('status_pembayaran_id', [2, 3])->sum('jumlah_bayar');
        $lunas = (float) $pesanan->pembayaran()->where('status_pembayaran_id', 3)->sum('jumlah_bayar');

        $isPelunasan = $lunas >= $pesanan->nominalDP() && $lunas < (float) $pesanan->total_tagihan;
        $amount = $isPelunasan
            ? max(0, (float) $pesanan->total_tagihan - $lunas)
            : max(0, $pesanan->nominalDP() - $dpTerbayar);

        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'Tagihan sudah lunas.'], 422);
        }

        $orderId = $pesanan->nomor_pesanan.($isPelunasan ? '-LNS-' : '-DP-').time();

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => optional($pesanan->pelanggan)->nama ?? 'Pelanggan',
                'phone' => optional($pesanan->pelanggan)->no_telepon ?? '',
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menyiapkan pembayaran: '.$e->getMessage()], 500);
        }

        PaymentTransaction::create([
            'order_id' => $orderId,
            'din_number' => $pesanan->nomor_pesanan,
            'gross_amount' => (int) $amount,
            'payment_type' => 'snap',
            'transaction_status' => 'pending',
            'raw_response' => ['snap_token' => $snapToken],
        ]);

        return response()->json(['success' => true, 'snap_token' => $snapToken, 'order_id' => $orderId]);
    }

    /**
     * Webhook Callback dari Midtrans (Main Flow Step 7 & 8)
     */
    public function notificationCallback(Request $request)
    {
        $payload = $request->getContent();
        $notification = json_decode($payload);

        $validSignatureKey = hash('sha512', $notification->order_id.$notification->status_code.$notification->gross_amount.config('midtrans.server_key'));

        if ($notification->signature_key != $validSignatureKey) {
            return response(['message' => 'Invalid signature'], 403);
        }

        $transaction = $notification->transaction_status;
        $type = $notification->payment_type;
        $orderIdFull = $notification->order_id;
        $kodePesanan = explode('-'.(strpos($orderIdFull, '-LNS-') !== false ? 'LNS' : 'DP').'-', $orderIdFull)[0];
        $fraud = $notification->fraud_status;

        $pesanan = Pesanan::where('nomor_pesanan', $kodePesanan)->first();

        if (! $pesanan) {
            return response(['message' => 'Order not found'], 404);
        }

        if (($transaction == 'capture' && $type == 'credit_card' && $fraud != 'challenge') || $transaction == 'settlement') {
            $this->processSuccessPayment($pesanan, $orderIdFull, $type, (float) $notification->gross_amount);
        } elseif ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            PaymentTransaction::where('order_id', $orderIdFull)->update(['transaction_status' => $transaction]);
        }

        return response(['message' => 'Success']);
    }

    /**
     * Proses pembayaran sukses (DP atau Pelunasan) — idempotent per order_id.
     */
    public function processSuccessPayment(Pesanan $pesanan, string $orderIdFull = '', string $paymentType = 'qris', float $grossAmount = 0)
    {
        $isPelunasan = strpos($orderIdFull, '-LNS-') !== false;

        // Guard idempotent: order_id yang sama tidak boleh membuat pembayaran ganda
        if (Pembayaran::where('nomor_referensi', $orderIdFull)->exists()) {
            return;
        }

        $metodeId = match ($paymentType) {
            'bank_transfer', 'transfer', 'echannel' => 3,
            'credit_card' => 4,
            default => 2, // qris / lainnya
        };

        DB::transaction(function () use ($pesanan, $orderIdFull, $isPelunasan, $metodeId, $grossAmount, $paymentType) {
            Pembayaran::create([
                'nomor_pembayaran' => 'PAY-'.date('YmdHis').'-'.rand(100, 999),
                'pesanan_id' => $pesanan->id,
                'metode_pembayaran_id' => $metodeId,
                'status_pembayaran_id' => 3, // LUNAS
                'jenis_pembayaran_id' => $isPelunasan ? 3 : 2, // PELUNASAN / UANG_MUKA
                'jumlah_bayar' => $grossAmount,
                'nomor_referensi' => $orderIdFull,
                'dibayar_pada' => now(),
                'catatan' => 'Pembayaran otomatis via Midtrans ('.($isPelunasan ? 'Pelunasan' : 'Uang Muka').')',
            ]);

            if ($pesanan->status_pesanan_id !== 5 && $pesanan->status_pesanan_id !== 6) {
                $pesanan->update(['status_pesanan_id' => 2]); // DIKONFIRMASI
            }

            PaymentTransaction::where('order_id', $orderIdFull)->update([
                'transaction_status' => 'settlement',
                'payment_type' => $paymentType,
                'gross_amount' => (int) $grossAmount,
            ]);
        });
    }

    /**
     * Alternate Flow 6a: Pengecekan status manual ke API Payment Gateway (Polling Fallback)
     */
    public function checkStatusManual($kodePesanan)
    {
        $pesanan = Pesanan::where('nomor_pesanan', $kodePesanan)->first();

        if (! $pesanan) {
            return back()->with('error', 'Pesanan tidak ditemukan.');
        }

        // Simulasikan pengecekan jika di local
        if (config('app.env') === 'local') {
            $transaksi = PaymentTransaction::where('din_number', $kodePesanan)
                ->where('transaction_status', '!=', 'settlement')
                ->latest()
                ->first();

            if ($transaksi) {
                $this->processSuccessPayment($pesanan, $transaksi->order_id, $transaksi->payment_type ?: 'qris', (float) $transaksi->gross_amount);
            }

            return redirect()->route('pesanan.bayar', $kodePesanan)
                ->with('success', 'Status pembayaran berhasil diverifikasi! Pesanan kini '.strtoupper('terkonfirmasi'));
        }

        try {
            $midtransStatus = \Midtrans\Transaction::status($kodePesanan);
            $transaction = $midtransStatus->transaction_status ?? '';

            if (in_array($transaction, ['settlement', 'capture'])) {
                $this->processSuccessPayment($pesanan, $kodePesanan);

                return redirect()->route('pesanan.bayar', $kodePesanan)
                    ->with('success', 'Status transaksi diverifikasi otomatis: LUNAS!');
            } elseif (in_array($transaction, ['deny', 'cancel', 'expire'])) {
                return redirect()->route('pesanan.bayar', $kodePesanan)
                    ->with('error', 'Pembayaran gagal atau expired. Silakan coba bayar ulang sisa tagihan.');
            } else {
                return redirect()->route('pesanan.bayar', $kodePesanan)
                    ->with('info', 'Status pembayaran saat ini: '.strtoupper($transaction));
            }
        } catch (\Exception $e) {
            Log::error('Manual Midtrans Polling Error: '.$e->getMessage());

            return redirect()->route('pesanan.bayar', $kodePesanan)
                ->with('error', 'Gagal memeriksa status ke Payment Gateway: '.$e->getMessage());
        }
    }
}
