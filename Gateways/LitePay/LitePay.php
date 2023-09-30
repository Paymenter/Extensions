<?php

namespace App\Extensions\Gateways\LitePay;

use App\Classes\Extensions\Gateway;
use App\Helpers\ExtensionHelper;
use Illuminate\Http\Request;
use App\Extensions\Gateways\LitePay\litepayclass;

class LitePay extends Gateway
{
    public function getMetadata()
    {
        return [
            'display_name' => 'LitePay',
            'version' => '1.0.0',
            'author' => 'Paymenter',
            'website' => 'https://paymenter.org',
        ];
    }
    
    public function getConfig()
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

    public function pay($total, $products, $invoiceId)
    {
        $litepay = new litepayclass('merchant');
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

    public function webhook(Request $request)
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
}
