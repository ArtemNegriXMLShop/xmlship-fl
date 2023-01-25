<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use BenSampo\Enum\Commands\MakeEnumCommand as BaseMakeEnumCommand;

class MakeEnumCommand extends BaseMakeEnumCommand
{

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\\Data\\Enums';
    }

}