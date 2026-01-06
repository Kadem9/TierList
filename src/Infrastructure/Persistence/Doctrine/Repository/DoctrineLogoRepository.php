<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Model\Logo;
use App\Domain\Port\LogoRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineLogo;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineLogoRepository implements LogoRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(Logo $logo): void
    {
        $doctrineLogo = DoctrineLogo::fromDomain($logo);
        $this->entityManager->persist($doctrineLogo);
        $this->entityManager->flush();
    }

    public function findAll(): array
    {
        $doctrineLogos = $this->entityManager
            ->getRepository(DoctrineLogo::class)
            ->findAll();

        return array_map(
            fn(DoctrineLogo $doctrineLogo) => $doctrineLogo->toDomain(),
            $doctrineLogos
        );
    }

    public function findByInternalId(string $id): ?Logo
    {
        $doctrineLogo = $this->entityManager
            ->getRepository(DoctrineLogo::class)
            ->find($id);

        return $doctrineLogo?->toDomain();
    }

    public function findByName(string $name): ?Logo
    {
        $doctrineLogo = $this->entityManager
            ->getRepository(DoctrineLogo::class)
            ->findOneBy(['name' => $name]);

        return $doctrineLogo?->toDomain();
    }

    public function countAll(): int
    {
        return $this->entityManager
            ->getRepository(DoctrineLogo::class)
            ->count([]);
    }
}

