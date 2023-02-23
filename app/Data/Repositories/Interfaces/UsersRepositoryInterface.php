<?php

namespace App\Data\Repositories\Interfaces;

use App\Data\DataTransferObjects\User as UserDto;
use App\Data\Models\User;

interface UsersRepositoryInterface
{
    public function create(UserDto $user): void;

    public function findByEmail(string $email): ?User;
}
