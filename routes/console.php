<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule: Cek stok menipis setiap jam
Schedule::command('stok:check-menipis')
    ->hourly()
    ->description('Cek dan buat notifikasi stok menipis/habis setiap jam');

// Atau bisa juga setiap 15 menit
// Schedule::command('stok:check-menipis')
//     ->everyFifteenMinutes()
//     ->description('Cek dan buat notifikasi stok menipis/habis');

// Auto cancel pesanan yang telat lunas
Schedule::command('pesanan:auto-cancel')
    ->daily()
    ->description('Batalkan pesanan yang belum lunas melewati batas hari pelunasan');