<?php

use Illuminate\Support\Facades\Route;
use OneClick\OCPay\Http\Controllers\OCPayController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('ocpay')->group(function () {
        Route::get('/redirect', [OCPayController::class, 'redirect'])->name('ocpay.redirect');
        Route::get('/callback', [OCPayController::class, 'callback'])->name('ocpay.callback');
    });
});
