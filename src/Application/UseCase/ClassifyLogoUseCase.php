<?php

namespace App\Application\UseCase;

use App\Domain\Exception\LogoNotFoundException;
use App\Domain\Exception\PaymentRequiredException;
use App\Domain\Model\TierCategory;
use App\Domain\Model\TierList;
use App\Domain\Model\TierListItem;
use App\Domain\Model\User;
use App\Domain\Port\LogoRepositoryInterface;
use App\Domain\Port\TierListRepositoryInterface;

class ClassifyLogoUseCase
{
    public function __construct(
        private readonly TierListRepositoryInterface $tierListRepository,
        private readonly LogoRepositoryInterface $logoRepository
    ) {
    }

    public function execute(User $user, string $logoId, TierCategory $tierCategory): void
    {
        if (!$user->isPremium()) {
            throw new PaymentRequiredException();
        }

        $logo = $this->logoRepository->findByInternalId($logoId);
        if ($logo === null) {
            throw new LogoNotFoundException($logoId);
        }

        $tierList = $this->tierListRepository->findByUser($user);

        if ($tierList === null) {
            $tierListId = uniqid('tierlist_', true);
            $tierList = new TierList($tierListId, $user);
        }

        $existingItem = null;
        foreach ($tierList->getItems() as $item) {
            if ($item->getLogo()->getId() === $logoId) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem !== null) {
            $existingItem->setTierCategory($tierCategory);
        } else {
            $newItem = new TierListItem($logo, $tierCategory);
            $tierList->addItem($newItem);
        }

        $this->tierListRepository->save($tierList);
    }
}

