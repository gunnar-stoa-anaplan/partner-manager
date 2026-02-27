<?php

namespace App\Providers;

use App\Contracts\IdeaRoom\AiGateway;
use App\Services\IdeaRoom\Ai\AnthropicGateway;
use App\Services\IdeaRoom\Ai\OpenAiGateway;
use App\Services\IdeaRoom\FrameDetectionService;
use App\Services\IdeaRoom\GalleryThemeService;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class IdeaRoomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GalleryThemeService::class);
        $this->app->bind('idea_room.template_registry', GalleryThemeService::class);

        $this->app->singleton(FrameDetectionService::class, function () {
            $config = config('idea_room.ai_providers.openai');

            return new FrameDetectionService(new OpenAiGateway(
                apiKey: $config['api_key'] ?? '',
                baseUrl: $config['base_url'],
                model: $config['model'],
                embeddingModel: $config['embedding_model'],
            ));
        });

        $this->app->singleton(AiGateway::class, function () {
            $provider = config('idea_room.ai_provider');
            $providerConfig = config("idea_room.ai_providers.{$provider}");

            return match ($provider) {
                'openai' => new OpenAiGateway(
                    apiKey: $providerConfig['api_key'] ?? '',
                    baseUrl: $providerConfig['base_url'],
                    model: $providerConfig['model'],
                    embeddingModel: $providerConfig['embedding_model'],
                ),
                'anthropic' => new AnthropicGateway(
                    apiKey: $providerConfig['api_key'] ?? '',
                    baseUrl: $providerConfig['base_url'],
                    model: $providerConfig['model'],
                    maxTokens: $providerConfig['max_tokens'],
                ),
                default => throw new RuntimeException("Unsupported AI provider: {$provider}"),
            };
        });
    }

    public function boot(): void
    {
        //
    }
}
