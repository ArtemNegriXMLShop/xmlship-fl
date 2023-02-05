<?php

namespace App\EntryPoints\Http\Auth\ActionsProcessors;

use App\Foundation\Abstracts\Processor;
use App\Foundation\Interfaces\ProcessorInterface;
use App\Foundation\Interfaces\RequestInterface;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Validation\ValidationException;

class AuthForgotPasswordProcessor extends Processor implements ProcessorInterface
{
    private PasswordBroker $passwordBroker;

    public function __construct(
        private readonly PasswordBrokerManager $brokerManager,
    ) {
        $this->passwordBroker = $this->brokerManager->broker('users');
    }

    /** @throws ValidationException */
    public function execute(RequestInterface $request): array
    {
        $this->passwordBroker->sendResetLink($request->only('email'));

        return [];
    }
}
