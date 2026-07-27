<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes — Midtrans QRIS Payment Gateway
|--------------------------------------------------------------------------
*/

Route::post('/payment/qris', [PaymentController::class, 'createQris'])->name('api.payment.qris');
Route::get('/payment/status/{orderId}', [PaymentController::class, 'checkStatus'])->name('api.payment.status');
Route::post('/payment/notification', [PaymentController::class, 'handleNotification'])->name('api.payment.notification');
