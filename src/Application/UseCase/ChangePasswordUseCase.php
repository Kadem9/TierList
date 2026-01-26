<?php

namespace App\Application\UseCase;

use App\Domain\Port\UserRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function execute(DoctrineUser $doctrineUser, string $currentPassword, string $newPassword): void
    {
        if (!$this->passwordHasher->isPasswordValid($doctrineUser, $currentPassword)) {
            throw new \InvalidArgumentException('Le mot de passe actuel est incorrect.');
        }

        if (strlen($newPassword) < 6) {
            throw new \InvalidArgumentException('Le nouveau mot de passe doit contenir au moins 6 caractères.');
        }

        $hashedPassword = $this->passwordHasher->hashPassword($doctrineUser, $newPassword);

        $user = $doctrineUser->toDomain();
        $user->setPassword($hashedPassword);

        $this->userRepository->update($user);
    }
}
