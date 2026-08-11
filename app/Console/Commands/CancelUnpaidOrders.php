<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CancelUnpaidOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pesanan:auto-cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Batalkan otomatis pesanan yang belum lunas setelah melewati batas hari pelunasan (H-3/batas_konfirmasi_hari)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Menjalankan pengecekan pesanan kedaluwarsa...");

        $pesanans = \App\Models\Pesanan::with(['jadwal_pesanan', 'detail_pesanan.menu.ketentuan_paket'])
            ->whereIn('jenis_pesanan_id', [2, 3]) // Katering & Nasi Box
            ->whereNotIn('status_pesanan_id', [5, 6]) // Bukan Selesai/Batal
            ->where('status_pembayaran_id', '!=', 5) // Belum Lunas
            ->whereHas('jadwal_pesanan')
            ->get();

        $count = 0;
        foreach ($pesanans as $pesanan) {
            $batasHari = 3; // Default H-3
            $firstDetail = $pesanan->detail_pesanan->first();
            
            if ($firstDetail && $firstDetail->menu && $firstDetail->menu->ketentuan_paket) {
                $batasHari = (int) $firstDetail->menu->ketentuan_paket->batas_konfirmasi_hari ?: 3;
            }

            $tanggalAcara = \Carbon\Carbon::parse($pesanan->jadwal_pesanan->tanggal_acara)->startOfDay();
            $batasWaktu = $tanggalAcara->copy()->subDays($batasHari);

            if (now()->startOfDay()->greaterThanOrEqualTo($batasWaktu)) {
                // Cancel pesanan
                $pesanan->update([
                    'status_pesanan_id' => 6, // Dibatalkan
                    'catatan' => trim($pesanan->catatan . ' [BATAL OTOMATIS: Tidak bayar Pelunasan]'),
                ]);

                app(\App\Services\OrderService::class)->restoreStockPesanan($pesanan);

                if ($pesanan->pengantaran) {
                    $pesanan->pengantaran->update(['status_pengantaran_id' => 5]);
                }

                $this->line("Pesanan {$pesanan->id_pesanan} dibatalkan otomatis.");
                $count++;
            }
        }

        $this->info("Selesai! $count pesanan telah dibatalkan.");
    }
}
