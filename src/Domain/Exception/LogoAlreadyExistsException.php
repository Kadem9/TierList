<?php

namespace App\Domain\Exception;

class LogoAlreadyExistsException extends \Exception
{
    public function __construct(string $identifier)
    {
        parent::__construct(sprintf('Un logo avec l\'identifiant "%s" existe déjà.', $identifier));
    }
}

