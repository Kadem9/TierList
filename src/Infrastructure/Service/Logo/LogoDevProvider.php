<?php

namespace App\Infrastructure\Service\Logo;

use App\Domain\Port\LogoProviderInterface;

class LogoDevProvider implements LogoProviderInterface
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getLogoUrl(string $domain, int $size = 400): string
    {
        return sprintf(
            'https://img.logo.dev/%s?token=%s&size=%d',
            urlencode($domain),
            urlencode($this->apiKey),
            $size
        );
    }
}

