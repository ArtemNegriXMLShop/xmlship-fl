<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Foundation\Console\ScopeMakeCommand as BaseScopeMakeCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:scope')]
class ScopeMakeCommand extends BaseScopeMakeCommand
{
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Data\\Models\\Scopes';
    }
}
