<?php

namespace App\Services\IdeaRoom\Ai;

use App\Contracts\IdeaRoom\AiGateway;
use App\Exceptions\IdeaRoom\AiGatewayException;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class OpenAiGateway implements AiGateway
{
    /** @var string[] Models that don't support temperature */
    private const REASONING_MODEL_PREFIXES = ['o1', 'o3', 'o4', 'gpt-5'];

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
        private readonly string $embeddingModel,
    ) {}

    private function isReasoningModel(string $model): bool
    {
        foreach (self::REASONING_MODEL_PREFIXES as $prefix) {
            if (str_starts_with($model, $prefix)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function chat(array $request): array
    {
        $model = $request['model'] ?? $this->model;
        $maxTokens = $request['max_tokens'] ?? 2048;

        $body = array_filter([
            'model' => $model,
            'messages' => $request['messages'],
            'tools' => $request['tools'] ?? null,
            'response_format' => $request['response_format'] ?? null,
            'temperature' => $this->isReasoningModel($model) ? null : ($request['temperature'] ?? 0.7),
            'max_completion_tokens' => $maxTokens,
        ], fn ($value) => $value !== null);

        try {
            $response = Http::withToken($this->apiKey)
                ->baseUrl($this->baseUrl)
                ->retry(3, 200, throw: false)
                ->timeout(120)
                ->post('/chat/completions', $body);
        } catch (ConnectionException $e) {
            throw AiGatewayException::unavailable($e->getMessage());
        }

        if (! $response->successful()) {
            throw AiGatewayException::fromResponse(
                $response->status(),
                $response->body(),
            );
        }

        $data = $response->json();
        $choice = $data['choices'][0] ?? [];
        $message = $choice['message'] ?? [];

        return [
            'content' => $message['content'] ?? '',
            'tool_calls' => $message['tool_calls'] ?? [],
            'usage' => $data['usage'] ?? [],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function stream(array $request): Generator
    {
        $model = $request['model'] ?? $this->model;
        $maxTokens = $request['max_tokens'] ?? 2048;

        $body = array_filter([
            'model' => $model,
            'messages' => $request['messages'],
            'tools' => $request['tools'] ?? null,
            'temperature' => $this->isReasoningModel($model) ? null : ($request['temperature'] ?? 0.7),
            'max_completion_tokens' => $maxTokens,
            'stream' => true,
        ], fn ($value) => $value !== null);

        try {
            $response = Http::withToken($this->apiKey)
                ->baseUrl($this->baseUrl)
                ->timeout(60)
                ->withOptions(['stream' => true])
                ->post('/chat/completions', $body);
        } catch (ConnectionException $e) {
            throw AiGatewayException::unavailable($e->getMessage());
        }

        if (! $response->successful()) {
            throw AiGatewayException::fromResponse(
                $response->status(),
                $response->body(),
            );
        }

        $stream = $response->toPsrResponse()->getBody();
        $buffer = '';

        while (! $stream->eof()) {
            $buffer .= $stream->read(1024);
            $lines = explode("\n", $buffer);
            $buffer = array_pop($lines);

            foreach ($lines as $line) {
                $line = trim($line);

                if ($line === '' || $line === 'data: [DONE]') {
                    continue;
                }

                if (str_starts_with($line, 'data: ')) {
                    $json = json_decode(substr($line, 6), true);

                    if ($json !== null) {
                        yield $json;
                    }
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function embed(string $text): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->baseUrl($this->baseUrl)
                ->retry(3, 200, throw: false)
                ->timeout(30)
                ->post('/embeddings', [
                    'model' => $this->embeddingModel,
                    'input' => $text,
                ]);
        } catch (ConnectionException $e) {
            throw AiGatewayException::unavailable($e->getMessage());
        }

        if (! $response->successful()) {
            throw AiGatewayException::fromResponse(
                $response->status(),
                $response->body(),
            );
        }

        return $response->json('data.0.embedding', []);
    }
}
