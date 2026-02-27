<?php

namespace App\Exceptions\IdeaRoom;

use RuntimeException;
use Throwable;

class AiGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $statusCode = 500,
        public readonly ?string $providerError = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }

    public static function rateLimited(): self
    {
        return new self('AI provider rate limit exceeded', 429);
    }

    public static function unavailable(string $reason = ''): self
    {
        return new self("AI provider unavailable: {$reason}", 503);
    }

    public static function fromResponse(int $httpStatus, string $body): self
    {
        $message = match (true) {
            $httpStatus === 429 => 'AI provider rate limit exceeded',
            $httpStatus >= 500 => 'AI provider server error',
            $httpStatus === 401 => 'AI provider authentication failed',
            default => 'AI provider request failed',
        };

        return new self($message, $httpStatus, $body);
    }
}
