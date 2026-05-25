<?php

namespace App\Libraries;

class StripePayment
{
    private array $config;
    private string $apiError = '';

    public function __construct(array $paymentKeys)
    {
        $this->config = [
            'stripe_api_key' => $paymentKeys['stripe_api_key'] ?? '',
            'stripe_publishable_key' => $paymentKeys['stripe_publishable_key'] ?? '',
            'stripe_currency' => $paymentKeys['stripe_currency'] ?? '',
        ];

        $stripeLibrary = APPPATH . 'ThirdParty/stripe-php/init.php';
        if (is_file($stripeLibrary)) {
            require_once $stripeLibrary;
        }

        if (class_exists('\Stripe\Stripe') && $this->config['stripe_api_key'] !== '') {
            \Stripe\Stripe::setApiKey($this->config['stripe_api_key']);
        }
    }

    public function addCustomer(string $email, string $token)
    {
        if (! class_exists('\Stripe\Customer')) {
            $this->apiError = 'Stripe PHP library is not available.';
            return false;
        }

        try {
            return \Stripe\Customer::create([
                'email' => $email,
                'source' => $token,
            ]);
        } catch (\Throwable $error) {
            $this->apiError = $error->getMessage();
            return false;
        }
    }

    public function createCharge(string $customerId, string $itemName, float $itemPrice, string $orderId)
    {
        if (! class_exists('\Stripe\Charge')) {
            $this->apiError = 'Stripe PHP library is not available.';
            return false;
        }

        try {
            $charge = \Stripe\Charge::create([
                'customer' => $customerId,
                'amount' => (int) round($itemPrice * 100),
                'currency' => $this->config['stripe_currency'],
                'description' => $itemName,
                'metadata' => [
                    'order_id' => $orderId,
                ],
            ]);

            return $charge->jsonSerialize();
        } catch (\Throwable $error) {
            $this->apiError = $error->getMessage();
            return false;
        }
    }

    public function apiError(): string
    {
        return $this->apiError;
    }
}
