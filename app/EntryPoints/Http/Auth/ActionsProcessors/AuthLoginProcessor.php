<?php

namespace App\EntryPoints\Http\Auth\ActionsProcessors;

use App\Data\Repositories\Interfaces\UsersRepositoryInterface;
use App\Foundation\Abstracts\Processor;
use App\Foundation\Interfaces\ProcessorInterface;
use App\Foundation\Interfaces\RequestInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthLoginProcessor extends Processor implements ProcessorInterface
{
    public function __construct(private readonly UsersRepositoryInterface $usersRepository)
    {
    }

    /** @throws ValidationException */
    public function execute(RequestInterface $request): array
    {
        $user = $this->usersRepository->findByEmail($request->get('email'));

        if (!$user || !Hash::check($request->get('password'), $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->tokens()->delete();

        return [
            'token' => $user->createToken('personal-access-token')->plainTextToken,
        ];
    }
}
