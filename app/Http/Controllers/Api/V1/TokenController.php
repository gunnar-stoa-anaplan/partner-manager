<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TokenController extends Controller
{
    private const VALID_ABILITIES = [
        'imports:write',
        'imports:read',
        'partners:read',
        'enablement:read',
    ];

    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens()->orderByDesc('created_at')->get()->map(fn ($token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at?->toIso8601String(),
            'created_at' => $token->created_at->toIso8601String(),
        ]);

        return response()->json(['data' => $tokens]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'abilities' => 'required|array|min:1',
            'abilities.*' => 'string|in:' . implode(',', self::VALID_ABILITIES),
        ]);

        $token = $request->user()->createToken(
            $validated['name'],
            $validated['abilities']
        );

        return response()->json([
            'message' => 'Token created. Copy it now — it will not be shown again.',
            'token' => $token->plainTextToken,
            'abilities' => $validated['abilities'],
        ], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $token = $request->user()->tokens()->findOrFail($id);
        $token->delete();

        return response()->json(['message' => 'Token revoked.']);
    }
}
