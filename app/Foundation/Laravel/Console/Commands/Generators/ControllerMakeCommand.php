<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Routing\Console\ControllerMakeCommand as BaseControllerMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:controller')]
class ControllerMakeCommand extends \Illuminate\Routing\Console\ControllerMakeCommand
{

}