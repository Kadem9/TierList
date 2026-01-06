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

    public function generatePdf(TierList $tierList): string
    {
        // Générer le HTML d'abord
        $html = $this->generateHtml($tierList);

        // Créer une instance Dompdf
        $dompdf = new Dompdf();

        // Charger le HTML dans Dompdf
        $dompdf->loadHtml($html);

        // Configurer les options
        $dompdf->setPaper('A4', 'portrait');

        // Rendre le PDF
        $dompdf->render();

        // Retourner le contenu binaire du PDF
        return $dompdf->output();
    }
}

