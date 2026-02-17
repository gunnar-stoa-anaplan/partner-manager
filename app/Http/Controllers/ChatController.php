<?php

namespace App\Http\Controllers;

use App\Contracts\ChatServiceInterface;
use App\Services\ChatContextService;
use Generator;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function stream(Request $request, ChatServiceInterface $chatService, ChatContextService $contextService): StreamedResponse
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $context = $contextService->buildContext($request->user());

        return response()->stream(function () use ($chatService, $request, $context): Generator {
            yield from $chatService->streamResponse($request->input('message'), $context);
        });
    }
}
