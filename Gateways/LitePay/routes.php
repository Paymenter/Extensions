<?php

include_once __DIR__ . '/index.php';

use Illuminate\Support\Facades\Route;

Route::get('/litepay/webhook', function () {
    return LitePay_webhook(request());
});