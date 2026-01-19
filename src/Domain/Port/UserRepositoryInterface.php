<?php

namespace App\Domain\Port;

use App\Domain\Model\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findByEmail(string $email): ?User;

    public function findById(string $id): ?User;

    public function update(User $user): void;
}

