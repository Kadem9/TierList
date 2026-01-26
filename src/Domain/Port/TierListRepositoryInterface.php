<?php

namespace App\Domain\Port;

use App\Domain\Model\TierList;
use App\Domain\Model\User;

interface TierListRepositoryInterface
{
    public function save(TierList $tierList): void;

    public function findByUser(User $user): ?TierList;

    public function getGlobalStatistics(): array;
}



