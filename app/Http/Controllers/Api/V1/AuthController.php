<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AuthLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    private const DEFAULT_ABILITIES = [
        'imports:write',
        'imports:read',
        'partners:read',
        'enablement:read',
    ];

    public function login(AuthLoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $user = Auth::user();
        $abilities = $request->input('abilities', self::DEFAULT_ABILITIES);

        $token = $user->createToken(
            $request->input('device_name'),
            $abilities
        );

        return response()->json([
            'message' => 'Authenticated successfully.',
            'token' => $token->plainTextToken,
            'abilities' => $abilities,
        ], 201);
    }
}
