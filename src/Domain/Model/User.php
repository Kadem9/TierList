<?php

namespace App\Domain\Model;

class User
{
    private string $id;
    private string $email;
    private string $password;
    private bool $isPremium;

    public function __construct(string $id, string $email, string $password, bool $isPremium = false)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
        $this->isPremium = $isPremium;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function isPremium(): bool
    {
        return $this->isPremium;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function setPremium(bool $isPremium): void
    {
        $this->isPremium = $isPremium;
    }
}

