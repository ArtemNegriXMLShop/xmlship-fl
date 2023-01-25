<?php

namespace App\Foundation\Laravel\Providers;


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
    }

    private function registerNewLaravelStructure(): void
    {

    }

    private function registerSpecification()
    {

    }
}
