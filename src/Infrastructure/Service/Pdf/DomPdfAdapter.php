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

    public function generateHtml(TierList $tierList): string
    {
        // logos par catégorie pour le template
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

        // Générer le HTML avec Twig
        $html = $this->twig->render('tier_list/pdf_export.html.twig', [
            'tierList' => $tierList,
            'logosByCategory' => $logosByCategory,
        ]);

        return $html;
    }
}

