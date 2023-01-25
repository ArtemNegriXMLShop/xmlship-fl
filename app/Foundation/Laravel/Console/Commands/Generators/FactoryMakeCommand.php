<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Database\Console\Factories\FactoryMakeCommand as BaseFactoryMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:factory')]
class FactoryMakeCommand extends BaseFactoryMakeCommandAlias
{

}