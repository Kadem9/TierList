<?php

namespace App\Application\UseCase;

use App\Domain\Exception\LogoNotFoundException;
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
        // On vérifie que le logo existe
        $logo = $this->logoRepository->findByInternalId($logoId);
        if ($logo === null) {
            throw new LogoNotFoundException($logoId);
        }

        // Puis on va récupérer la TierList de l'utilisateur ou en créer une nouvelle
        $tierList = $this->tierListRepository->findByUser($user);

        if ($tierList === null) {
            // Créer une nouvelle tier list avec un ID unique
            $tierListId = uniqid('tierlist_', true);
            $tierList = new TierList($tierListId, $user);
        }

        // Chercher si un item existe déjà pour ce logo
        $existingItem = null;
        foreach ($tierList->getItems() as $item) {
            if ($item->getLogo()->getId() === $logoId) {
                $existingItem = $item;
                break;
            }
        }

        if ($existingItem !== null) {
            // Mettre à jour la catégorie du logo existant
            $existingItem->setTierCategory($tierCategory);
        } else {
            // Créer un nouveau TierListItem
            $newItem = new TierListItem($logo, $tierCategory);
            $tierList->addItem($newItem);
        }

        // On enregistre pr finir la TierList
        $this->tierListRepository->save($tierList);
    }
}

