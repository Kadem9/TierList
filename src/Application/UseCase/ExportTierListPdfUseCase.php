<?php

namespace App\Application\UseCase;

use App\Domain\Model\User;
use App\Domain\Port\PdfGeneratorInterface;
use App\Domain\Port\PdfStorageInterface;
use App\Domain\Port\TierListRepositoryInterface;

class ExportTierListPdfUseCase
{
    public function __construct(
        private readonly TierListRepositoryInterface $tierListRepository,
        private readonly PdfGeneratorInterface $pdfGenerator,
        private readonly PdfStorageInterface $pdfStorage
    ) {
    }

    public function execute(User $user): string
    {
        // Récupérer la TierList de l'utilisateur
        $tierList = $this->tierListRepository->findByUser($user);

        if ($tierList === null) {
            throw new \RuntimeException('Aucune tier list trouvée pour cet utilisateur.');
        }

        // Générer le contenu PDF binaire via PdfGeneratorInterface
        $pdfContent = $this->pdfGenerator->generatePdf($tierList);

        // fichier unique avec extension .pdf
        $filename = sprintf('tierlist_%s_%s.pdf', $user->getId(), date('Y-m-d_His'));

        // Sauvegarder le contenu PDF via PdfStorageInterface
        $this->pdfStorage->store($filename, $pdfContent);

        // Retourner le filename pour pouvoir récupérer le contenu ensuite
        return $filename;
    }
}

