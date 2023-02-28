# Sofort Direct Payment Gateway for Stripe

## Installation
Download the code and place it in the `app/Extensions/Gateways/StripeSofort` directory.

## Configuration
In the admin panel, go to `Settings > Payment Gateways` and click on the `Stripe Sofort` gateway. Enter your your wanted country then press `Save`.

## IMPORTANT
You need to set the apikeys in the Stripe gateway settings. The Stripe Sofort gateway will use the same apikeys as the Stripe gateway.

## Usage
When a user selects the `Stripe Sofort` gateway, they will be redirected to the Stripe Sofort payment page. After the payment is completed, the user will be redirected back to the site.
