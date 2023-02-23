<?php

namespace App\Application\Observers;

use App\Application\Notifications\UserCreated;
use App\Data\Models\User;
use Illuminate\Auth\Passwords\PasswordBrokerManager;

final readonly class UserObserver
{
    public function __construct(private PasswordBrokerManager $brokerManager)
    {
    }

    public function created(User $user): void
    {
        $user->notify(new UserCreated($this->brokerManager->broker('users')->createToken($user)));
    }
}
