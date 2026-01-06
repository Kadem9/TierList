<?php

namespace App\Domain\Exception;

class LogoNotFoundException extends \Exception
{
    public function __construct(string $logoId)
    {
        parent::__construct(sprintf('Le logo avec l\'ID "%s" n\'existe pas.', $logoId));
    }
}

