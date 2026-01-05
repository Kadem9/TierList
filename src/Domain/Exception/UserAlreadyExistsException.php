<?php

namespace App\Domain\Exception;

class UserAlreadyExistsException extends \Exception
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Un utilisateur avec l\'email "%s" existe déjà.', $email));
    }
}

