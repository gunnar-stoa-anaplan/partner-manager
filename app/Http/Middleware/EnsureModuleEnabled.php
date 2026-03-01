<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\ModuleRegistry;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(
        private ModuleRegistry $registry,
    ) {}

    /**
     * @param  string  $identifier  Feature slug ("enablement") or feature.section ("enablement.calendar")
     */
    public function handle(Request $request, Closure $next, string $identifier): Response
    {
        $user = $request->user();
        $internalUser = $user instanceof User ? $user : null;

        [$feature] = $this->registry->parseIdentifier($identifier);
        $state = $internalUser
            ? $this->registry->getFeatureState($feature, $internalUser)
            : $this->registry->getFeatureState($feature);

        // Hidden features remain a hard 403
        if ($state === 'hidden') {
            abort(403, 'This module is not enabled for your instance.');
        }

        // Locked features redirect to training — unless Tell-phase is
        // completed, in which case the user needs module access for Show/Do.
        if ($state === 'locked') {
            if (! $this->registry->isAccessible($identifier, $internalUser)) {
                return redirect()->route('training.index', ['feature' => $feature])
                    ->with('info', 'Complete training to unlock this feature.');
            }
        }

        // Nudge: allow through but flash a training reminder (once per session)
        if ($state === 'nudge' && ! $request->session()->has("nudge_dismissed.{$feature}")) {
            $request->session()->flash('training_nudge', $feature);
        }

        // Enforce write permission for mutating requests (internal users with role_id only)
        if ($internalUser !== null && $internalUser->role_id !== null && ! $request->isMethodSafe() && ! $internalUser->canWriteModule($identifier)) {
            abort(403, 'You do not have write access to this module.');
        }

        return $next($request);
    }
}
