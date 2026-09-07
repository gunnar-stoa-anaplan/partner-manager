<?php

namespace App\Insights;

use App\Insights\Contracts\MetricSource;
use InvalidArgumentException;

/**
 * The catalog of metric sources, keyed by metric key. Domains register their
 * sources here (see InsightServiceProvider); the Insight endpoint and components
 * resolve by key. Mirrors ModuleRegistry in spirit.
 */
class MetricRegistry
{
    /** @var array<string, MetricSource> */
    private array $sources = [];

    public function register(MetricSource $source): void
    {
        $this->sources[$source->key()] = $source;
    }

    public function has(string $key): bool
    {
        return isset($this->sources[$key]);
    }

    public function get(string $key): MetricSource
    {
        return $this->sources[$key]
            ?? throw new InvalidArgumentException("Unknown metric [{$key}].");
    }

    /** @return array<string, MetricSource> */
    public function all(): array
    {
        return $this->sources;
    }
}
