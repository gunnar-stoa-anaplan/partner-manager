<?php

namespace App\Insights;

/**
 * The audit trail behind a number: the exact source rows, the SQL that produced
 * them, and provenance (which load/snapshot they came from).
 */
class MetricDrill
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, string>  $columns
     * @param  array<string, mixed>  $provenance
     */
    public function __construct(
        public readonly array $rows,
        public readonly array $columns,
        public readonly ?string $sql = null,
        public readonly array $provenance = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'rows' => $this->rows,
            'columns' => $this->columns,
            'sql' => $this->sql,
            'provenance' => $this->provenance,
        ];
    }
}
