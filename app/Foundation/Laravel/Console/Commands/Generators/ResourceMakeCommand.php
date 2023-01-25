<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Foundation\Console\ResourceMakeCommand as BaseResourceMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:resource')]
class ResourceMakeCommand extends BaseResourceMakeCommandAlias
{

}