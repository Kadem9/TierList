<?php

namespace App\Domain\Port;

interface EmailInterface
{
    public function sendEmail(string $to, string $subject, string $body): array;
}
