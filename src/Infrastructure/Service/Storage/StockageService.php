<?php

namespace App\Infrastructure\Service\Storage;

use App\Domain\Port\PdfStorageInterface;

class StockageService
{
    public function __construct(
        private readonly PdfStorageInterface $pdfStorage
    ) {
    }

    public function init(): void
    {
    }

    public function store(string $filename, string $content): string
    {
        return $this->pdfStorage->store($filename, $content);
    }

    public function getFileContent(string $filename): string
    {
        return $this->pdfStorage->getFileContent($filename);
    }
}

