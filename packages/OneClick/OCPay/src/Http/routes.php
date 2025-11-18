<?php

use Illuminate\Support\Facades\Route;
use OneClick\OCPay\Http\Controllers\OCPayController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('ocpay')->group(function () {
        Route::get('/redirect', [OCPayController::class, 'redirect'])->name('ocpay.redirect');
        Route::get('/callback', [OCPayController::class, 'callback'])->name('ocpay.callback');
    });

    // Admin polling endpoint
    Route::middleware(['admin'])->group(function () {
        Route::post('/admin/order/{order}/ocpay-status', [\OneClick\OCPay\Http\Controllers\AdminOCPayController::class, 'status'])
            ->name('admin.ocpay.status');
    });
});
