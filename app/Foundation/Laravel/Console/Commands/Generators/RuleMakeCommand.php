<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Foundation\Console\RuleMakeCommand as RuleMakeCommandBase;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:rule')]
class RuleMakeCommand extends RuleMakeCommandBase
{
    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Foundation\\Rules';
    }
}
