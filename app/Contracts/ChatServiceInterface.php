<?php

namespace App\Contracts;

use Generator;

interface ChatServiceInterface
{
    /**
     * Stream a response to a user's chat message.
     *
     * @return Generator<int, string, void, void>
     */
    public function streamResponse(string $message, array $context): Generator;
}
