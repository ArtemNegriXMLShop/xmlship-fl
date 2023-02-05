<?php

namespace App\Foundation\Laravel\Providers;


use App\Application\Observers\UserObserver;
use App\Data\Models\User;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerNewLaravelStructure();
        $this->registerSpecification();
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        User::observe(UserObserver::class);
    }

    private function registerNewLaravelStructure(): void
    {

    }

    private function registerSpecification()
    {

    }
}
