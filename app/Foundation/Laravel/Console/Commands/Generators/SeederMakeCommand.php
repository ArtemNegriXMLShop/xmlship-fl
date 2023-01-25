<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Database\Console\Seeds\SeederMakeCommand as BaseSeederMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:seeder')]
class SeederMakeCommand extends BaseSeederMakeCommandAlias
{

}