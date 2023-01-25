<?php

namespace App\Foundation\Laravel\Kernels;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Console extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
    }

    protected function commands(): void
    {
        $this->load(app_path('Foundation/Laravel/Console/Commands/Generators'));
        $this->load(app_path('Foundation/Laravel/Console/Commands'));
        $this->load(app_path('EntryPoints/Console/Commands'));
    }
}
