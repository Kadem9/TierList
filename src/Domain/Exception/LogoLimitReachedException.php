<?php

namespace App\Domain\Exception;

class LogoLimitReachedException extends \Exception
{
    public function __construct()
    {
        parent::__construct('La limite de 10 logos a été atteinte.');
    }
}

