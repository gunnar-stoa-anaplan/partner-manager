<?php

namespace App\Insights;

class MetricValue
{
    public function __construct(
        public readonly int $count,
        public readonly ?int $sum = null,
        public readonly string $unit = 'count',
        public readonly ?string $snapshotDate = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'count' => $this->count,
            'sum' => $this->sum,
            'unit' => $this->unit,
            'snapshot_date' => $this->snapshotDate,
        ];
    }
}
