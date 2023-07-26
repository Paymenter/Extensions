<?php

use Illuminate\Support\Facades\Route;

Route::get('/litepay/webhook', [\App\Extensions\Gateways\LitePay\LitePay::class, 'webhook']);