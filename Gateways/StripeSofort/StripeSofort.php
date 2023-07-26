<?php

namespace App\Extensions\Gateways\StripeSofort;

use Stripe\StripeClient;
use App\Helpers\ExtensionHelper;
use Illuminate\Http\Request;
use App\Classes\Extensions\Gateway;


class StripeSofort extends Gateway
{
    public function getUrl($total, $products, $orderId)
    {
        $client = $this->Client();
        $order = $client->paymentIntents->create([
            'confirm' => true,
            'amount' => $total * 100,
            'currency' => ExtensionHelper::getCurrency(),
            'payment_method_types' => ['sofort'],
            'payment_method_data' => ['type' => 'sofort', 'sofort' => ['country' => ExtensionHelper::getConfig('StripeSofort', 'country')]],
            'return_url' => route('clients.invoice.show', $orderId),
            'metadata' => [
                'user_id' => auth()->user()->id,
                'order_id' => $orderId,
            ],
        ]);

        return $order;
    }

    public function webhook($request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('stripe-signature');
        $endpoint_secret = ExtensionHelper::getConfig('Stripe', 'stripe_webhook_secret');
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit;
        }
        if ($event->type == 'checkout.session.completed') {
            $order = $event->data->object;
            $order_id = $order->metadata->order_id;
            ExtensionHelper::paymentDone($order_id);
        }
    }

    private function Client()
    {
        if (!ExtensionHelper::getConfig('Stripe', 'stripe_test_mode')) {
            $stripe = new StripeClient(
                ExtensionHelper::getConfig('Stripe', 'stripe_secret_key')
            );
        } else {
            $stripe = new StripeClient(
                ExtensionHelper::getConfig('Stripe', 'stripe_test_key')
            );
        }

        return $stripe;
    }

    public function pay($total, $products, $orderId)
    {
        $order = $this->getUrl($total, $products, $orderId);
        if ($order->status == 'requires_action' && $order->next_action->type == 'redirect_to_url') {
            $url = $order->next_action->redirect_to_url->url;
            return $url;
        }
        dd($order);
    }

    public function getConfig()
    {
        return [
            [
                'name' => 'country',
                'friendlyName' => 'Country short code',
                'type' => 'text',
                'description' => 'The country code for sofort. For example: DE, NL',
                'required' => true,
            ],
        ];
    }
}
