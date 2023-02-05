<?php

namespace App\EntryPoints\Http\Auth\ActionsProcessors;

use App\Data\Models\User;
use App\Data\Repositories\Interfaces\UsersRepositoryInterface;
use App\Foundation\Abstracts\Processor;
use App\Foundation\Interfaces\ProcessorInterface;
use App\Foundation\Interfaces\RequestInterface;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthNewPasswordProcessor extends Processor implements ProcessorInterface
{
    private PasswordBroker $passwordBroker;

    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository,
        private readonly PasswordBrokerManager $brokerManager,
    ) {
        $this->passwordBroker = $this->brokerManager->broker('users');
    }

    /** @throws ValidationException */
    public function execute(RequestInterface $request): array
    {
        $email = $request->get('email');
        $token = $request->get('token');
        $password = $request->get('password');

        $user = $this->usersRepository->findByEmail($email);

        if (!$user || !$this->passwordBroker->tokenExists($user, $token)) {
            throw new \RuntimeException('The provided token is incorrect or expired.');
        }

        $this->passwordBroker->deleteToken($user);
        $user->update(['password' => Hash::make($password)]);
        $user->tokens()->delete();

        $this->verifyUserEmail($user);

        return [
            'token' => $user->createToken('personal-access-token')->plainTextToken,
        ];
    }

    private function verifyUserEmail(User $user): void
    {
        !$user->email_verified_at && $user->update(['email_verified_at' => now()]);
    }
}
