<?php

namespace App\Domain\Port;

interface PdfStorageInterface
{
    public function store(string $filename, string $content): string;

    public function getFileContent(string $filename): string;
}

