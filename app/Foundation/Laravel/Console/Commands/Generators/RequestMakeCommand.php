<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Foundation\Console\RequestMakeCommand as BaseRequestMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:request')]
class RequestMakeCommand extends BaseRequestMakeCommandAlias
{

}