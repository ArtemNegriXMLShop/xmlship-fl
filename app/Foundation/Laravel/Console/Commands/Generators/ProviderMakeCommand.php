<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Foundation\Console\ProviderMakeCommand as BaseProviderMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:provider')]
class ProviderMakeCommand extends BaseProviderMakeCommandAlias
{
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Foundation\\Laravel\\Providers';
    }
}
