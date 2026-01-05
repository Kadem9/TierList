<?php

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Domain\Model\User;
use App\Domain\Port\UserRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineUserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function save(User $user): void
    {
        $doctrineUser = DoctrineUser::fromDomain($user);
        $this->entityManager->persist($doctrineUser);
        $this->entityManager->flush();
    }

    public function findByEmail(string $email): ?User
    {
        $doctrineUser = $this->entityManager
            ->getRepository(DoctrineUser::class)
            ->findOneBy(['email' => $email]);

        return $doctrineUser?->toDomain();
    }
}

