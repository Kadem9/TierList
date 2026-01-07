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

        // On peut générer le contenu HTML via PdfGeneratorInterface
        $htmlContent = $this->pdfGenerator->generateHtml($tierList);

        // fichier unique
        $filename = sprintf('tierlist_%s_%s.html', $user->getId(), date('Y-m-d_His'));

        // Sauvegarder le contenu via PdfStorageInterface
        $url = $this->pdfStorage->store($filename, $htmlContent);

        return $url;
    }
}

