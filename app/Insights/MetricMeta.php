<?php

namespace App\Insights;

class MetricMeta
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $unit = 'count',
        public readonly ?string $description = null,
        public readonly ?string $section = null,
        public readonly string $source = 'warehouse',
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'unit' => $this->unit,
            'description' => $this->description,
            'section' => $this->section,
            'source' => $this->source,
        ];
    }
}
