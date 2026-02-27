<?php

namespace App\Services\IdeaRoom\Ai;

use App\Contracts\IdeaRoom\AiGateway;
use App\Exceptions\IdeaRoom\AiGatewayException;
use Generator;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AnthropicGateway implements AiGateway
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
        private readonly int $maxTokens,
    ) {}

    /**
     * {@inheritDoc}
     */
    public function chat(array $request): array
    {
        $messages = $this->convertMessages($request['messages']);

        $body = array_filter([
            'model' => $request['model'] ?? $this->model,
            'messages' => $messages['messages'],
            'system' => $messages['system'],
            'tools' => $this->convertTools($request['tools'] ?? []),
            'temperature' => $request['temperature'] ?? 0.7,
            'max_tokens' => $request['max_tokens'] ?? $this->maxTokens,
        ], fn ($value) => $value !== null && $value !== []);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
            ])
                ->baseUrl($this->baseUrl)
                ->retry(3, 200, throw: false)
                ->timeout(60)
                ->post('/v1/messages', $body);
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

        return $this->normalizeResponse($data);
    }

    /**
     * {@inheritDoc}
     */
    public function stream(array $request): Generator
    {
        $messages = $this->convertMessages($request['messages']);

        $body = array_filter([
            'model' => $request['model'] ?? $this->model,
            'messages' => $messages['messages'],
            'system' => $messages['system'],
            'tools' => $this->convertTools($request['tools'] ?? []),
            'temperature' => $request['temperature'] ?? 0.7,
            'max_tokens' => $request['max_tokens'] ?? $this->maxTokens,
            'stream' => true,
        ], fn ($value) => $value !== null && $value !== []);

        try {
            $response = Http::withHeaders([
                'x-api-key' => $this->apiKey,
                'anthropic-version' => '2023-06-01',
            ])
                ->baseUrl($this->baseUrl)
                ->timeout(60)
                ->withOptions(['stream' => true])
                ->post('/v1/messages', $body);
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

                if ($line === '' || ! str_starts_with($line, 'data: ')) {
                    continue;
                }

                $json = json_decode(substr($line, 6), true);

                if ($json !== null) {
                    yield $json;
                }
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function embed(string $text): array
    {
        throw new AiGatewayException('Anthropic does not support embeddings. Use OpenAI for embeddings.', 501);
    }

    /**
     * Convert OpenAI-format messages to Anthropic format (extract system message).
     *
     * @return array{system: string|null, messages: array}
     */
    private function convertMessages(array $messages): array
    {
        $system = null;
        $converted = [];

        foreach ($messages as $message) {
            if ($message['role'] === 'system') {
                $system = $message['content'];

                continue;
            }

            if ($message['role'] === 'tool') {
                $converted[] = [
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'tool_result',
                            'tool_use_id' => $message['tool_call_id'],
                            'content' => $message['content'],
                        ],
                    ],
                ];

                continue;
            }

            if ($message['role'] === 'assistant' && ! empty($message['tool_calls'])) {
                $content = [];

                if (! empty($message['content'])) {
                    $content[] = ['type' => 'text', 'text' => $message['content']];
                }

                foreach ($message['tool_calls'] as $toolCall) {
                    $content[] = [
                        'type' => 'tool_use',
                        'id' => $toolCall['id'],
                        'name' => $toolCall['function']['name'],
                        'input' => json_decode($toolCall['function']['arguments'], true) ?? [],
                    ];
                }

                $converted[] = ['role' => 'assistant', 'content' => $content];

                continue;
            }

            $converted[] = [
                'role' => $message['role'],
                'content' => $message['content'],
            ];
        }

        return ['system' => $system, 'messages' => $converted];
    }

    /**
     * Convert OpenAI-format tool definitions to Anthropic format.
     */
    private function convertTools(array $tools): array
    {
        return array_map(fn (array $tool) => [
            'name' => $tool['function']['name'],
            'description' => $tool['function']['description'],
            'input_schema' => $tool['function']['parameters'],
        ], $tools);
    }

    /**
     * Normalize Anthropic response to match OpenAI format.
     *
     * @return array{content: string, tool_calls: array, usage: array}
     */
    private function normalizeResponse(array $data): array
    {
        $content = '';
        $toolCalls = [];

        foreach ($data['content'] ?? [] as $block) {
            if ($block['type'] === 'text') {
                $content .= $block['text'];
            }

            if ($block['type'] === 'tool_use') {
                $toolCalls[] = [
                    'id' => $block['id'],
                    'type' => 'function',
                    'function' => [
                        'name' => $block['name'],
                        'arguments' => json_encode($block['input']),
                    ],
                ];
            }
        }

        return [
            'content' => $content,
            'tool_calls' => $toolCalls,
            'usage' => [
                'prompt_tokens' => $data['usage']['input_tokens'] ?? 0,
                'completion_tokens' => $data['usage']['output_tokens'] ?? 0,
                'total_tokens' => ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0),
            ],
        ];
    }
}
