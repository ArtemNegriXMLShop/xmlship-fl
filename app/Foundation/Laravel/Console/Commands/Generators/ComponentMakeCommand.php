<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Foundation\Console\ComponentMakeCommand as BaseComponentMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:component')]
class ComponentMakeCommand extends BaseComponentMakeCommandAlias
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    final public function handle()
    {
        $this->components->error('We don\'t use "Component" yet.');
    }
}