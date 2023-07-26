<?php

use Illuminate\Support\Facades\Route;

Route::post('/paypal_ipn/webhook', [\App\Extensions\Gateways\PayPal_IPN\PayPal_IPN::class, 'webhook'])->name('paypal_ipn.webhook');
