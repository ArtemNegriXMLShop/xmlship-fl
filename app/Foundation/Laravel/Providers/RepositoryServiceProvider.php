<?php

namespace App\Foundation\Laravel\Providers;

use App\Data\Repositories\Interfaces\UsersRepositoryInterface;
use App\Data\Repositories\UsersRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public array $bindings = [
        UsersRepositoryInterface::class => UsersRepository::class,
    ];
}
