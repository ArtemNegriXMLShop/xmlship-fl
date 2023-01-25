<?php

namespace App\Foundation\Laravel\Console\Commands\Generators;

use Illuminate\Foundation\Console\ChannelMakeCommand as BaseChannelMakeCommandAlias;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'make:channel')]
class ChannelMakeCommand extends BaseChannelMakeCommandAlias
{
    /**
     * Execute the console command.
     *
     * @return void
     */
    final public function handle()
    {
        $this->components->error('We don\'t use "Channel" yet.');
    }
}