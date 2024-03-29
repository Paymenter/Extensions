<?php

use App\Helpers\ExtensionHelper;
use Illuminate\Http\Request;

function LitePay_getConfig()
{
    return [
        [
            'name' => 'secret',
            'friendlyName' => 'Secret',
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => 'merchant_id',
            'friendlyName' => 'VENDOR ID',
            'type' => 'text',
            'required' => true,
        ],
    ];
}

function LitePay_pay($total, $products, $invoiceId)
{
    include_once __DIR__ . '/litepay.php';

    $litepay = new litepay('merchant');
    $req = $litepay->__call('pay', [[
        'vendor' => ExtensionHelper::getConfig('LitePay', 'merchant_id'),
        'secret' => ExtensionHelper::getConfig('LitePay', 'secret'),
        'invoice' => $invoiceId,
        'price' => $total,
        'currency' => ExtensionHelper::getCurrency(),
        'callbackUrl' => url('/extensions/litepay/webhook') . '?invoiceId=' . $invoiceId . '&secret=' . ExtensionHelper::getConfig('LitePay', 'secret'),
        'returnUrl' => route('clients.invoice.show', $invoiceId),
    ]]);

    return $req->url;
}

function LitePay_webhook(Request $request)
{
    $input = $request->all();
    $invoiceId = $input['invoiceId'];
    $secret = $input['secret'];
    if (!isset($invoiceId) || !isset($secret))
        return;
    if ($secret !== ExtensionHelper::getConfig('LitePay', 'secret'))
        return;
    ExtensionHelper::paymentDone($invoiceId);

    // Return *ok*
    return response('*ok*');
}
