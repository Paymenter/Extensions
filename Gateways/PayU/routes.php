<?php

use Illuminate\Support\Facades\Route;

Route::post('/payu/cancel', [\App\Extensions\Gateways\PayU\PayU::class, 'cancel'])->name('payu.cancel');

Route::post('/payu/success', [\App\Extensions\Gateways\PayU\PayU::class, 'success'])->name('payu.success');
