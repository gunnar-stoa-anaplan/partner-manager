<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJourneyTokenMatches
{
    /**
     * Verify the bearer token is scoped to the journey in the URL.
     *
     * Tokens issued for journey runtime use are named "journey-agent:{journey_key}".
     * This middleware extracts the journey_key from the token name and confirms
     * it matches the {journey} route parameter.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $journey = $request->route('journey');
        $token = $request->user()?->currentAccessToken();

        if (! $journey || ! $token) {
            abort(403, 'Token not bound to a journey.');
        }

        $name = $token->name ?? '';
        if (! str_starts_with($name, 'journey-agent:')) {
            abort(403, 'Token is not a journey-agent token.');
        }

        $tokenJourneyKey = substr($name, strlen('journey-agent:'));
        if ($tokenJourneyKey !== $journey->journey_key) {
            abort(403, 'Token is bound to a different journey.');
        }

        return $next($request);
    }
}
