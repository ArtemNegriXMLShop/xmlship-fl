<?php

namespace App\Foundation\Laravel\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use App\EntryPoints\Http\Auth\AuthController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        $this->routes(function () {
            Route::get('login', function () {
                return redirect('/');
            })->name('login');

            Route::prefix('api/app/auth')
                ->post('/login', [AuthController::class, 'login'])->name('jwt.auth.login');

            Route::middleware(['AppAPI', 'auth:api'])
                ->group(static function () {
                    foreach (static::getFilesFromDirectory(app_path('EntryPoints/Routes/app')) as $file => $prefix) {
                        Route::prefix($prefix)
                            ->prefix('api/app')
                            ->namespace('\App\EntryPoints\Http')
                            ->group($file);
                    }
                });

            Route::middleware('ClientsAPI')
                ->group(static function () {
                    foreach (
                        static::getFilesFromDirectory(
                            app_path('EntryPoints/Routes/external')
                        ) as $file => $prefix
                    ) {
                        Route::prefix($prefix)
                            ->prefix('api/external')
                            ->namespace('\App\EntryPoints\HttpExternal')
                            ->group($file);
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
            $return[$file->getPathname()] =
                trim(Str::replace([$dir, '.php'], '', $file->getPathname()), '/');
        }

        return $return;
    }
}
