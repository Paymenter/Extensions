<?php

namespace App\Extensions\Gateways\PayU;

use App\Helpers\ExtensionHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Classes\Extensions\Gateway;

class PayU extends Gateway
{
    public function getConfig()
    {
        return [
            [
                'name' => 'merchant_key',
                'friendlyName' => 'Merchant Key',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'merchant_salt',
                'friendlyName' => 'Merchant Salt V1',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'test_mode',
                'friendlyName' => 'Test Mode',
                'type' => 'boolean',
                'required' => false,
            ],
            [
                'name' => 'test_merchant_key',
                'friendlyName' => 'Test Merchant Key',
                'type' => 'text',
                'required' => false,
            ],
            [
                'name' => 'test_merchant_salt',
                'friendlyName' => 'Test Merchant Salt V1',
                'type' => 'text',
                'required' => false,
            ],
        ];
    }

    public function pay($total, $products, $invoiceId)
    {
        $apiKey = ExtensionHelper::getConfig('PayU', 'test_mode') ? ExtensionHelper::getConfig('PayU', 'test_merchant_key') : ExtensionHelper::getConfig('PayU', 'merchant_key');
        $salt = ExtensionHelper::getConfig('PayU', 'test_mode') ? ExtensionHelper::getConfig('PayU', 'test_merchant_salt') : ExtensionHelper::getConfig('PayU', 'merchant_salt');
        $txnId = $invoiceId;
        $amount = $total;
        $productInfo = $products[0]->name;
        $firstName = auth()->user()->name;
        $email = auth()->user()->email;
        $phone = auth()->user()->phone;
        $surl = route('payu.success');
        $furl = route('payu.cancel');
        $hashString = "$apiKey|$txnId|$amount|$productInfo|$firstName|$email|||||||||||$salt";
        $hash = hash('sha512', $hashString);

        $data = array(
            'key' => $apiKey,
            'txnid' => $txnId,
            'amount' => $amount,
            'productinfo' => $productInfo,
            'firstname' => $firstName,
            'email' => $email,
            'phone' => $phone,
            'surl' => $surl,
            'furl' => $furl,
            'hash' => $hash,
        );
        if (ExtensionHelper::getConfig('PayU', 'test_mode')) {
            $url = "https://test.payu.in/_payment";
        } else {
            $url = "https://secure.payu.in/_payment";
        }

        echo "<form method='post' action='" . $url . "' name='payuForm'>";
        foreach ($data as $key => $value) {
            echo "<input type='hidden' name='" . $key . "' value='" . $value . "' />";
        }
        echo "<input type='submit' value='Click here if you are not redirected automatically' /></form>";
        echo "<script type='text/javascript'>document.payuForm.submit();</script>";
        exit;
    }



    public function success(Request $request)
    {
        $posted = $request->all();
        $orderId = $posted['txnid'];
        $apiKey = ExtensionHelper::getConfig('PayU', 'test_mode') ? ExtensionHelper::getConfig('PayU', 'test_merchant_key') : ExtensionHelper::getConfig('PayU', 'merchant_key');
        $salt = ExtensionHelper::getConfig('PayU', 'test_mode') ? ExtensionHelper::getConfig('PayU', 'test_merchant_salt') : ExtensionHelper::getConfig('PayU', 'merchant_salt');
        $hashString = "$apiKey|verify_payment|$orderId|$salt";
        $hash = hash('sha512', $hashString);

        $data = array(
            'key' => $apiKey,
            'command' => 'verify_payment',
            'var1' => $orderId,
            'hash' => $hash,
        );

        if (ExtensionHelper::getConfig('PayU', 'test_mode')) {
            $url = "https://test.payu.in/merchant/postservice?form=2";
        } else {
            $url = "https://info.payu.in/merchant/postservice?form=2";
        }

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->asForm()->post($url, $data);

        $response = $response->json();

        if ($response['transaction_details'][$orderId]['status'] == 'success') {
            ExtensionHelper::paymentDone($orderId);
            return redirect()->route('clients.invoice.show', $orderId)->with('success', 'Payment Successful');
        } else {
            return redirect()->route('clients.invoice.show', $orderId)->with('error', 'Payment Failed');
        }
    }

    public function cancel(Request $request)
    {
        $posted = $request->all();
        return redirect()->route('clients.invoice.show', $posted['txnid'])->with('error', 'Payment Cancelled');
    }
}
