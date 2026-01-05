<?php

namespace App\Application\UseCase;

use App\Domain\Exception\LogoAlreadyExistsException;
use App\Domain\Exception\LogoLimitReachedException;
use App\Domain\Model\Logo;
use App\Domain\Port\LogoRepositoryInterface;

class AddLogoUseCase
{
    private const MAX_LOGOS = 10;

    public function __construct(
        private readonly LogoRepositoryInterface $logoRepository
    ) {
    }

    public function execute(string $id, string $name, string $url): void
    {
        // Vérifier la limite de 10 logos
        if ($this->logoRepository->countAll() >= self::MAX_LOGOS) {
            throw new LogoLimitReachedException();
        }

        // Vérifier si un logo avec le même ID existe déjà
        if ($this->logoRepository->findByInternalId($id) !== null) {
            throw new LogoAlreadyExistsException($id);
        }

        // Créer l'objet Logo et le sauvegarder
        $logo = new Logo($id, $name, $url);
        $this->logoRepository->save($logo);
    }
}

