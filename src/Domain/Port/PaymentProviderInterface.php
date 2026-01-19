<?php

namespace App\Domain\Port;

interface PaymentProviderInterface
{
    public function createCheckoutSession(string $userId, string $successUrl, string $cancelUrl): string;
}
