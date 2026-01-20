<?php

namespace App\Infrastructure\Service\Pdf;

use App\Domain\Model\TierList;
use App\Domain\Port\PdfGeneratorInterface;
use Dompdf\Dompdf;
use Twig\Environment;

class DomPdfAdapter implements PdfGeneratorInterface
{
    public function __construct(
        private readonly Environment $twig
    ) {
    }

    public function generateHtml(TierList $tierList, array $globalStats = []): string
    {
        $logosByCategory = [
            'S' => [],
            'A' => [],
            'B' => [],
            'C' => [],
            'D' => [],
        ];

        foreach ($tierList->getItems() as $item) {
            $category = $item->getTierCategory()->value;
            $logosByCategory[$category][] = $item->getLogo();
        }

        return $this->twig->render('tier_list/pdf_export.html.twig', [
            'tierList' => $tierList,
            'logosByCategory' => $logosByCategory,
            'globalStats' => $globalStats,
        ]);
    }

    public function generatePdf(TierList $tierList, array $globalStats = []): string
    {
        $html = $this->generateHtml($tierList, $globalStats);

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}

