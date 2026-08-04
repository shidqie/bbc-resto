<?php

namespace App\Console\Commands;

use App\Services\StokNotificationService;
use Illuminate\Console\Command;

class CheckStokMenipis extends Command
{
    protected $signature = 'stok:check-menipis';
    protected $description = 'Cek dan buat notifikasi stok menipis/habis';

    public function handle()
    {
        $this->info('Memeriksa stok bahan baku...');

        $service = app(StokNotificationService::class);
        $created = $service->checkAndNotify();

        $this->info("Pengecekan selesai. {$created} notifikasi baru dibuat.");

        return Command::SUCCESS;
    }
}