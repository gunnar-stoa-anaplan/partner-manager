<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix('portal')
                ->name('portal.')
                ->group(base_path('routes/portal.php'));

            // Load module route files (web)
            foreach (['integration-hub', 'strategy', 'idea-room'] as $mod) {
                $slug = str_replace('-', '_', $mod);
                $file = base_path("routes/modules/{$mod}.php");

                if (file_exists($file)) {
                    Route::middleware(['web', 'auth', "module:{$slug}"])
                        ->prefix($mod)
                        ->name("{$slug}.")
                        ->group($file);
                }
            }

            // Load module API route files
            $ideaRoomApi = base_path('routes/modules/idea-room-api.php');

            if (file_exists($ideaRoomApi)) {
                Route::middleware(['web', 'auth', 'module:idea_room'])
                    ->prefix('api/v1/idea-room')
                    ->name('idea_room.api.')
                    ->group($ideaRoomApi);
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            'portal' => \App\Http\Middleware\PortalAuth::class,
            'portal.role' => \App\Http\Middleware\PortalRole::class,
            'portal.can' => \App\Http\Middleware\PortalCan::class,
            'module' => \App\Http\Middleware\EnsureModuleEnabled::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\RedirectRestrictedRoles::class,
        ]);
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
