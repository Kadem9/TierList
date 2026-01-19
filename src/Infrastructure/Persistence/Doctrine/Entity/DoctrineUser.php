<?php

namespace App\Infrastructure\Persistence\Doctrine\Entity;

use App\Domain\Model\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class DoctrineUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string')]
    private string $password;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $isPremium = false;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function setIsPremium(bool $isPremium): void
    {
        $this->isPremium = $isPremium;
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function toDomain(): User
    {
        return new User(
            $this->id,
            $this->email,
            $this->password,
            $this->isPremium
        );
    }

    public static function fromDomain(User $user): self
    {
        $doctrineUser = new self();
        $doctrineUser->id = $user->getId();
        $doctrineUser->email = $user->getEmail();
        $doctrineUser->password = $user->getPassword();
        $doctrineUser->isPremium = $user->isPremium();

        return $doctrineUser;
    }
}

