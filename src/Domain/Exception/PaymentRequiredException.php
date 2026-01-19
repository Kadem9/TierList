<?php

namespace App\Domain\Exception;

class PaymentRequiredException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Paiement requis pour accéder à cette fonctionnalité.');
    }
}
