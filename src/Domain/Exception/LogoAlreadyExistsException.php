<?php

namespace App\Domain\Exception;

class LogoAlreadyExistsException extends \Exception
{
    public function __construct(string $id)
    {
        parent::__construct(sprintf('Un logo avec l\'ID "%s" existe déjà.', $id));
    }
}

