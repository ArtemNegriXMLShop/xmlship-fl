<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Database\Console\Migrations\MigrateMakeCommand as BaseMigrateMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:migration')]
class MigrateMakeCommand extends BaseMigrateMakeCommandAlias
{

}