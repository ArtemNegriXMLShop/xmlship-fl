<?php

namespace App\Data\DataTransferObjects;

readonly class User
{
    public function __construct(public string $email, public string $name, public ?string $password = null)
    {
    }
}
