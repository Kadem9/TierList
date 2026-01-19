<?php

namespace App\Infrastructure\Service\Payment;

use App\Domain\Port\PaymentProviderInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripePaymentAdapter implements PaymentProviderInterface
{
    public function __construct(private readonly string $secretKey)
    {
        Stripe::setApiKey($this->secretKey);
    }

    public function createCheckoutSession(string $userId, string $successUrl, string $cancelUrl): string
    {
        $session = Session::create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Tier List Premium',
                    ],
                    'unit_amount' => 999,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => $userId,
            'ui_mode' => 'hosted',
        ]);

        return $session->url;
    }
}
