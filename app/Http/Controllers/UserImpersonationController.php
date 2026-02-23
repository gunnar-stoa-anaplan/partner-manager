<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserImpersonationController extends Controller
{
    /**
     * Start impersonating another system user.
     */
    public function start(Request $request, User $user): RedirectResponse
    {
        $admin = auth()->user();

        if ($user->id === $admin->id) {
            return back()->with('error', 'You cannot impersonate yourself.');
        }

        $request->session()->put('impersonating_user', true);
        $request->session()->put('impersonator_id', $admin->id);
        $request->session()->put('impersonator_name', $admin->name);

        Auth::login($user);

        return redirect()->route('welcome')->with('success', "Now viewing as {$user->name}.");
    }

    /**
     * Stop impersonating and return to the admin account.
     */
    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('impersonator_id');

        if (! $adminId) {
            return redirect()->route('welcome');
        }

        $admin = User::findOrFail($adminId);

        $request->session()->forget([
            'impersonating_user',
            'impersonator_id',
            'impersonator_name',
        ]);

        Auth::login($admin);

        return redirect()->route('users.index')->with('success', 'Returned to your admin account.');
    }
}
