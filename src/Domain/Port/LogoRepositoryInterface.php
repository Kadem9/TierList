<?php

namespace App\Domain\Port;

use App\Domain\Model\Logo;

interface LogoRepositoryInterface
{
    public function save(Logo $logo): void;

    public function findAll(): array;

    public function findByInternalId(string $id): ?Logo;
}

