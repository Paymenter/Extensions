<?php

use Illuminate\Support\Facades\Route;

include_once __DIR__ . '/index.php';

Route::post('/payu/cancel', function () {
    return UPay_cancel(request());
})->name('payu.cancel');

Route::post('/payu/success', function () {
    return UPay_success(request());
})->name('payu.success');
