<?php

namespace App\EntryPoints\Http\Auth\ActionsProcessors;

use App\Data\Repositories\Interfaces\UsersRepositoryInterface;
use App\Foundation\Abstracts\Processor;
use App\Foundation\Interfaces\ProcessorInterface;
use App\Foundation\Interfaces\RequestInterface;
use Carbon\Factory as Carbon;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthLoginProcessor extends Processor implements ProcessorInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $usersRepository,
        private readonly Config $config,
        private readonly Carbon $carbon,
    ) {
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

        $expiresIn = $this->config->get('sanctum.expiration');
        $expiresAt = $this->carbon->now()->addSeconds($expiresIn);

        $personalAccessToken = $user->createToken(name: 'personal-access-token', expiresAt: $expiresAt);

        return [
            'access_token' => $personalAccessToken->plainTextToken,
            'token_type' =>  'bearer',
            'expires_in' =>  $expiresIn,
        ];
    }
}
