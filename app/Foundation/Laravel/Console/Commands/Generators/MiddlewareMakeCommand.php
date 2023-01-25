<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Routing\Console\MiddlewareMakeCommand as BaseMiddlewareMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:middleware')]
class MiddlewareMakeCommand extends BaseMiddlewareMakeCommandAlias
{
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Foundation\\Laravel\\Middleware';
    }

}
