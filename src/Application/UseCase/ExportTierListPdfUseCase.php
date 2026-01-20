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
        $tierList = $this->tierListRepository->findByUser($user);

        if ($tierList === null) {
            throw new \RuntimeException('Aucune tier list trouvée pour cet utilisateur.');
        }

        $globalStats = $this->tierListRepository->getGlobalStatistics();

        $pdfContent = $this->pdfGenerator->generatePdf($tierList, $globalStats);

        $filename = sprintf('tierlist_%s_%s.pdf', $user->getId(), date('Y-m-d_His'));

        $this->pdfStorage->store($filename, $pdfContent);

        return $filename;
    }
}

