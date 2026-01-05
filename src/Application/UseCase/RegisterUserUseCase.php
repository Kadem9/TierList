<?php

namespace App\Application\UseCase;

use App\Domain\Exception\UserAlreadyExistsException;
use App\Domain\Model\User;
use App\Domain\Port\UserRepositoryInterface;
use App\Infrastructure\Persistence\Doctrine\Entity\DoctrineUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function execute(string $id, string $email, string $plainPassword): void
    {
        // Vérifier si l'utilisateur existe déjà
        if ($this->userRepository->findByEmail($email) !== null) {
            throw new UserAlreadyExistsException($email);
        }

        // Créer un DoctrineUser temporaire pour le hashage du mot de passe
        // (UserPasswordHasherInterface nécessite un objet implémentant UserInterface)
        $temporaryDoctrineUser = new DoctrineUser();
        $temporaryDoctrineUser->setId($id);
        $temporaryDoctrineUser->setEmail($email);
        $hashedPassword = $this->passwordHasher->hashPassword($temporaryDoctrineUser, $plainPassword);

        // Créer l'utilisateur du domaine avec le mot de passe hashé
        $user = new User($id, $email, $hashedPassword);
        $this->userRepository->save($user);
    }
}

