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
                ->prefix('docs')
                ->name('docs.')
                ->group(base_path('routes/docs.php'));

            Route::middleware('web')
                ->prefix('portal')
                ->name('portal.')
                ->group(base_path('routes/portal.php'));

            Route::middleware('web')
                ->prefix('workshops')
                ->name('workshop-guides.')
                ->group(base_path('routes/workshop-guides.php'));

            // Zoom Chat Webhook (no auth — Zoom server-to-server)
            $zoomWebhook = base_path('routes/modules/zoom-chat-webhook.php');

            if (file_exists($zoomWebhook)) {
                Route::middleware('api')
                    ->prefix('api/webhooks')
                    ->name('webhooks.')
                    ->group($zoomWebhook);
            }

            // Load module route files (web)
            $moduleRoutes = [
                'integration-hub' => 'integration_hub.hub',
                'strategy' => 'strategy.strategy',
                'idea-room' => 'idea_room.room',
                'zoom-chat' => 'zoom_chat.chat',
            ];

            foreach ($moduleRoutes as $mod => $moduleIdentifier) {
                $slug = str_replace('-', '_', $mod);
                $file = base_path("routes/modules/{$mod}.php");

                if (file_exists($file)) {
                    Route::middleware(['web', 'auth', "module:{$moduleIdentifier}"])
                        ->prefix($mod)
                        ->name("{$slug}.")
                        ->group($file);
                }
            }

            // Knowledge Base (gated by ai_knowledge.knowledge_base)
            $knowledgeBaseWeb = base_path('routes/modules/knowledge-base.php');

            if (file_exists($knowledgeBaseWeb)) {
                Route::middleware(['web', 'auth', 'module:ai_knowledge.knowledge_base'])
                    ->prefix('knowledge-base')
                    ->name('knowledge_base.')
                    ->group($knowledgeBaseWeb);
            }

            // Load module API route files
            $ideaRoomApi = base_path('routes/modules/idea-room-api.php');

            if (file_exists($ideaRoomApi)) {
                Route::middleware(['web', 'auth', 'module:idea_room.room'])
                    ->prefix('api/v1/idea-room')
                    ->name('idea_room.api.')
                    ->group($ideaRoomApi);
            }

            $knowledgeBaseApi = base_path('routes/modules/knowledge-base-api.php');

            if (file_exists($knowledgeBaseApi)) {
                Route::middleware(['web', 'auth', 'module:ai_knowledge.knowledge_base'])
                    ->prefix('api/v1/knowledge-base')
                    ->name('knowledge_base.api.')
                    ->group($knowledgeBaseApi);
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
            'admin_or_integration' => \App\Http\Middleware\EnsureAdminOrIntegration::class,
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
