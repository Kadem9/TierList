<?php

namespace App\Domain\Port;

use App\Domain\Model\TierList;

interface PdfGeneratorInterface
{
    public function generateHtml(TierList $tierList): string;

    public function generatePdf(TierList $tierList): string;
}

