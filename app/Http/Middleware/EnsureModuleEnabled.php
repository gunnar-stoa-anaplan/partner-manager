<?php

namespace App\Http\Middleware;

use App\Services\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        private ModuleRegistry $registry,
    ) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! $this->registry->isEnabled($module)) {
            abort(403, 'This module is not enabled for your instance.');
        }

        return $next($request);
    }
}
