<?php

namespace App\Data\Repositories;

use App\Data\DataTransferObjects\User as UserDto;
use App\Data\Models\User;
use App\Data\Repositories\Interfaces\UsersRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

final class UsersRepository implements UsersRepositoryInterface
{
    public function create(UserDto $user): void
    {
        User::query()->create([
            'email' => $user->email,
            'name' => $user->name,
            'password' => Hash::make($user->password ?? Str::random(8)),
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }
}
