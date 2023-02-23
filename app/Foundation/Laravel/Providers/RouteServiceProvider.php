<?php

namespace App\Foundation\Laravel\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    public const HOME = '/home';

    public function boot()
    {
        $this->routes(function () {
            Route::get('login', static fn () => redirect('/'))->name('login');

            Route::middleware(['AppAPI'])->prefix('api/app')->group(static function () {
                $routers = static::getFilesFromDirectory(app_path('EntryPoints/Routes/app'));

                foreach ($routers as $file => $prefix) {
                    Route::namespace('\App\EntryPoints\Http')->group($file);
                }
            });

            Route::middleware(['ClientsAPI'])->prefix('api/external')->group(static function () {
                $routers = static::getFilesFromDirectory(app_path('EntryPoints/Routes/external'));

                foreach ($routers as $file => $prefix) {
                    Route::namespace('\App\EntryPoints\HttpExternal')->group($file);
                }
            });
        });
    }

    private static function getFilesFromDirectory(string $dir): array
    {
        $return = [];
        if (!File::isDirectory($dir)) {
            return [];
        }

        $files = File::allFiles($dir);

        foreach ($files as $file) {
            $return[$file->getPathname()] = trim(Str::replace([$dir, '.php'], '', $file->getPathname()), '/');
        }

        return $return;
    }
}
