<?php

use Illuminate\Support\Facades\Route;

include_once __DIR__ . '/index.php';

Route::post('/stripesofort/webhook', function () {
    StripeSofort_webhook(request());
});
