<?php

use Illuminate\Support\Facades\Route;

Route::post('/stripesofort/webhook', [\App\Extensions\Gateways\StripeSofort\StripeSofort::class, 'webhook'])->name('stripesofort.webhook');
