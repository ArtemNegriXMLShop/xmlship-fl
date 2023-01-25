<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Foundation\Console\TestMakeCommand as BaseTestMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:test')]
class TestMakeCommand extends BaseTestMakeCommandAlias
{

}