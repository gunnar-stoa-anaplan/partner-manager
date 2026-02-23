<?php

namespace App\Contracts\IdeaRoom;

use Generator;

interface AiGateway
{
    /**
     * Send a chat completion request.
     *
     * @param  array{
     *   model?: string,
     *   messages: array<int, array{role: string, content: string}>,
     *   tools?: array,
     *   temperature?: float,
     *   max_tokens?: int,
     * }  $request
     * @return array{content: string, tool_calls: array, usage: array}
     */
    public function chat(array $request): array;

    /**
     * Stream a chat completion response.
     *
     * @param  array{
     *   model?: string,
     *   messages: array<int, array{role: string, content: string}>,
     *   tools?: array,
     *   temperature?: float,
     *   max_tokens?: int,
     * }  $request
     */
    public function stream(array $request): Generator;

    /**
     * Generate an embedding vector for the given text.
     *
     * @return float[]
     */
    public function embed(string $text): array;
}
