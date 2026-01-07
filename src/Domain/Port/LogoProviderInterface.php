<?php

namespace App\Domain\Port;

interface LogoProviderInterface
{
    public function getLogoUrl(string $domain, int $size = 400): string;
}

