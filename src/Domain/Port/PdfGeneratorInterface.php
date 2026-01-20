<?php

namespace App\Domain\Port;

use App\Domain\Model\TierList;

interface PdfGeneratorInterface
{
    public function generateHtml(TierList $tierList, array $globalStats = []): string;

    public function generatePdf(TierList $tierList, array $globalStats = []): string;
}

