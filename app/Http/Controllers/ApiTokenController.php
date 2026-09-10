<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    /**
     * Valid token abilities.
     */
    private const ABILITIES = [
        'imports:write' => 'Import data (opportunities, projects, contacts)',
        'imports:read' => 'View import status and history',
        'partners:read' => 'Read partner data, pipeline, and projects',
        'enablement:read' => 'Read enablement calendar and events',
    ];

    /**
     * Display the token management page.
     */
    public function index()
    {
        $tokens = PersonalAccessToken::with('tokenable')
            ->orderByDesc('created_at')
            ->get();

        $users = User::with('role')->orderBy('name')->get(['id', 'name', 'email', 'role_id']);

        return view('admin.api-tokens.index', [
            'tokens' => $tokens,
            'users' => $users,
            'abilities' => self::ABILITIES,
        ]);
    }

    /**
     * Create a new API token.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'in:' . implode(',', array_keys(self::ABILITIES)),
        ]);

        $user = User::findOrFail($request->user_id);
        $token = $user->createToken($request->name, $request->abilities);

        return redirect()->route('admin.api-tokens')
            ->with('success', 'Token created successfully.')
            ->with('new_token', $token->plainTextToken);
    }

    /**
     * Revoke (delete) an API token.
     */
    public function destroy($id)
    {
        $token = PersonalAccessToken::findOrFail($id);
        $token->delete();

        return redirect()->route('admin.api-tokens')
            ->with('success', 'Token revoked successfully.');
    }
}
