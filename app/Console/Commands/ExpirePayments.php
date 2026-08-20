<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pembayaran;
use App\Models\Pesanan;
use Illuminate\Support\Facades\DB;
use App\Services\OrderService;

class ExpirePayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengecek dan membatalkan sesi pembayaran atau pesanan yang kedaluwarsa';

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        $this->info('Starting payment expiration check...');

        DB::transaction(function () use ($orderService) {
            // 1. Kedaluwarsa Sesi DP -> Batalkan Pesanan
            $expiredDps = Pembayaran::where('status_verifikasi', 'belum_dibayar')
                ->where('jenis_pembayaran', 'uang_muka')
                ->where('expires_at', '<', now())
                ->get();

            foreach ($expiredDps as $dp) {
                $dp->update(['status_verifikasi' => 'kedaluwarsa', 'catatan_verifikasi' => 'Sesi DP habis otomatis']);
                
                $pesanan = Pesanan::find($dp->pesanan_id);
                if ($pesanan && $pesanan->status_pesanan_id !== 6) {
                    $pesanan->update([
                        'status_pesanan_id' => 6,
                        'alasan_batal' => 'Dibatalkan oleh sistem (Melewati batas waktu pembayaran DP)'
                    ]); // Dibatalkan
                    $orderService->restoreStockPesanan($pesanan);
                    $this->info("Pesanan {$pesanan->id_pesanan} dibatalkan karena DP kedaluwarsa.");
                }
            }

            // 2. Kedaluwarsa Sesi Pelunasan (Hanya sesinya, pesanan aman jika belum lewat batas H-3)
            $expiredPelunasan = Pembayaran::where('status_verifikasi', 'belum_dibayar')
                ->where('jenis_pembayaran', 'pelunasan')
                ->where('expires_at', '<', now())
                ->get();
            
            foreach ($expiredPelunasan as $pelunasan) {
                $pelunasan->update(['status_verifikasi' => 'kedaluwarsa', 'catatan_verifikasi' => 'Sesi pelunasan habis otomatis']);
                $this->info("Sesi pelunasan untuk Pesanan ID {$pelunasan->pesanan_id} kedaluwarsa.");
            }

            // 3. Batas Pelunasan H-4 Terlewati -> Dibatalkan
            $pastDeadlinePesanan = Pesanan::where('status_pembayaran_id', 3) // Menunggu Pelunasan
                ->whereNotNull('batas_pelunasan')
                ->where('batas_pelunasan', '<', now())
                ->where('status_pesanan_id', '!=', 6)
                ->get();

            foreach ($pastDeadlinePesanan as $pesanan) {
                $pesanan->update(['status_pesanan_id' => 6, 'alasan_batal' => 'Dibatalkan oleh sistem (Batas H-4 lewat)']);
                $this->info("Pesanan {$pesanan->id_pesanan} dibatalkan otomatis (Batas H-4 lewat).");
            }
        });

        $this->info('Payment expiration check completed.');
    }
}
