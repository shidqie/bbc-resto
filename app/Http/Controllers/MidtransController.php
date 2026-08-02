<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReceiptMail;
use App\Models\BuktiPembayaran;
use App\Models\NotifikasiAdmin;
use App\Models\PesananCatering;
use App\Models\PesananDinein;
use App\Models\PesananNasiBox;
use App\Models\PesananStatusLog;
use App\Services\PesananCateringService;
use App\Services\PesananNasiBoxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

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
     * Generate Midtrans Snap Token
     * Dynamically calculates gross_amount for DP or Pelunasan (Sisa Tagihan)
     */
    public static function generateSnapToken($order, $type = 'catering')
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');

        $gross_amount = 0;
        $order_id_prefix = $order->kode_pesanan;

        if ($order instanceof PesananDinein) {
            foreach ($order->items as $item) {
                $harga = $item->menu->harga ?? 0;
                $gross_amount += ($item->qty * $harga);
            }
            $firstName = $order->nama_konsumen ?? 'Pelanggan Dine-In';
            $phone = '080000000000';
        } else {
            // Jika status terkonfirmasi/menunggu_pelunasan -> Nominal = Sisa Tagihan Pelunasan
            if (in_array($order->status, ['terkonfirmasi', 'menunggu_pelunasan'])) {
                $gross_amount = max(0, $order->total_tagihan - $order->dp_amount);
                $order_id_prefix .= '-LNS';
            } else {
                // Nominal = DP Amount
                $gross_amount = $order->dp_amount;
                $order_id_prefix .= '-DP';
            }
            $firstName = $order->nama_pemesan;
            $phone = $order->kontak;
        }

        $params = [
            'transaction_details' => [
                'order_id' => $order_id_prefix.'-'.time(),
                'gross_amount' => (int) $gross_amount,
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
            Log::error('Midtrans Snap Error: '.$e->getMessage());

            return null;
        }
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
        $orderIdArray = explode('-', $orderIdFull);
        $kodePesanan = $orderIdArray[0];
        $fraud = $notification->fraud_status;

        $order = null;
        if (strpos($kodePesanan, 'CTR') === 0) {
            $order = PesananCatering::where('kode_pesanan', $kodePesanan)->first();
        } elseif (strpos($kodePesanan, 'NBX') === 0) {
            $order = PesananNasiBox::where('kode_pesanan', $kodePesanan)->first();
        } elseif (strpos($kodePesanan, 'DIN') === 0) {
            $order = PesananDinein::with('items.menu')->where('kode_pesanan', $kodePesanan)->first();
        }

        if (! $order) {
            return response(['message' => 'Order not found'], 404);
        }

        if (($transaction == 'capture' && $type == 'credit_card' && $fraud != 'challenge') || $transaction == 'settlement') {
            $this->processSuccessPayment($order, $orderIdFull);
        } elseif ($transaction == 'deny' || $transaction == 'expire' || $transaction == 'cancel') {
            // Alternate Flow 5a: Jika pembayaran DP gagal/expired -> batalkan. Jika pelunasan gagal, tetap terkonfirmasi untuk retry.
            if ($order->status === 'menunggu_dp') {
                $order->update(['status' => 'dibatalkan']);
            }
        }

        return response(['message' => 'Success']);
    }

    /**
     * Helper untuk memproses pembayaran sukses (DP atau Pelunasan)
     */
    public function processSuccessPayment($order, $orderIdFull = '')
    {
        $isPelunasan = strpos($orderIdFull, '-LNS-') !== false || in_array($order->status, ['terkonfirmasi', 'menunggu_pelunasan']);

        if ($isPelunasan) {
            if ($order->status_bayar !== 'lunas') {
                $statusLama = $order->status;
                $newStatus = in_array($order->status, ['ditinjau', 'lunas']) ? 'terkonfirmasi' : $order->status;
                $order->update(['status' => $newStatus, 'status_bayar' => 'lunas']);

                PesananStatusLog::create([
                    'pesanan_type' => get_class($order),
                    'pesanan_id' => $order->id,
                    'status_lama' => $statusLama,
                    'status_baru' => 'lunas',
                    'user_id' => null,
                    'catatan' => 'Pelunasan otomatis via Midtrans',
                ]);

                // Catat di Riwayat Pembayaran
                BuktiPembayaran::create([
                    'pesanan_id' => $order->id,
                    'pesanan_type' => get_class($order),
                    'jenis_pembayaran' => 'pelunasan',
                    'file_path' => 'midtrans_online',
                    'status' => 'verified',
                    'catatan_admin' => 'Pelunasan LUNAS via Payment Gateway (Midtrans)',
                ]);

                // Notifikasi Admin Pelunasan
                NotifikasiAdmin::buatNotifikasi(
                    'PELUNASAN DITERIMA #'.$order->kode_pesanan,
                    'Pelunasan sebesar Rp '.number_format(max(0, $order->total_tagihan - $order->dp_amount), 0, ',', '.')." untuk pesanan #{$order->kode_pesanan} atas nama {$order->nama_pemesan} telah LUNAS via Payment Gateway.",
                    'pelunasan',
                    '/pesan/bayar/'.$order->kode_pesanan
                );

                // Send Email Invoice / Notification Lunas (Main Flow Step 9)
                if ($order->email) {
                    try {
                        Mail::to($order->email)->send(new PaymentReceiptMail($order));
                    } catch (\Exception $e) {
                        Log::error('Midtrans Webhook Email Error: '.$e->getMessage());
                    }
                }
            }
        } else {
            if ($order->status !== 'terkonfirmasi' && $order->status !== 'lunas' && $order->status !== 'selesai') {
                $statusLama = $order->status;
                $order->update(['status' => 'terkonfirmasi', 'status_bayar' => 'dp_terbayar']);

                PesananStatusLog::create([
                    'pesanan_type' => get_class($order),
                    'pesanan_id' => $order->id,
                    'status_lama' => $statusLama,
                    'status_baru' => 'terkonfirmasi',
                    'user_id' => null,
                    'catatan' => 'DP diterima otomatis via Midtrans',
                ]);

                // Catat di Riwayat Pembayaran DP
                BuktiPembayaran::create([
                    'pesanan_id' => $order->id,
                    'pesanan_type' => get_class($order),
                    'jenis_pembayaran' => 'dp',
                    'file_path' => 'midtrans_online',
                    'status' => 'verified',
                    'catatan_admin' => 'DP Diterima via Payment Gateway (Midtrans)',
                ]);

                // Notifikasi Admin DP Diterima
                NotifikasiAdmin::buatNotifikasi(
                    'DP Diterima #'.$order->kode_pesanan,
                    'Pembayaran DP sebesar Rp '.number_format($order->dp_amount, 0, ',', '.')." untuk pesanan #{$order->kode_pesanan} atas nama {$order->nama_pemesan} telah diterima & terkonfirmasi.",
                    'bukti_pembayaran',
                    '/pesan/bayar/'.$order->kode_pesanan
                );

                if ($order instanceof PesananCatering) {
                    PesananCateringService::potongStok($order);
                } elseif ($order instanceof PesananNasiBox) {
                    PesananNasiBoxService::potongStok($order);
                }

                if ($order->email) {
                    try {
                        Mail::to($order->email)->send(new PaymentReceiptMail($order));
                    } catch (\Exception $e) {
                        Log::error('Midtrans Webhook Email Error: '.$e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Alternate Flow 6a: Pengecekan status manual ke API Payment Gateway (Polling Fallback)
     */
    public function checkStatusManual($kodePesanan)
    {
        $order = PesananCatering::where('kode_pesanan', $kodePesanan)->first();
        if (! $order) {
            $order = PesananNasiBox::where('kode_pesanan', $kodePesanan)->first();
        }

        if (! $order) {
            return back()->with('error', 'Pesanan tidak ditemukan.');
        }

        // Simulasikan pengecekan jika di local
        if (config('app.env') === 'local') {
            $this->processSuccessPayment($order, $order->kode_pesanan.($order->status === 'terkonfirmasi' ? '-LNS-' : '-DP-'));

            return redirect()->route('pesanan.bayar', $kodePesanan)
                ->with('success', 'Status pembayaran berhasil diverifikasi! Pesanan kini '.strtoupper($order->fresh()->status));
        }

        try {
            // Polling via Midtrans API
            $midtransStatus = Transaction::status($order->kode_pesanan);
            $transaction = $midtransStatus->transaction_status ?? '';

            if (in_array($transaction, ['settlement', 'capture'])) {
                $this->processSuccessPayment($order, $order->kode_pesanan);

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
