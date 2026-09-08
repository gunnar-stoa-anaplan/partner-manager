<?php

namespace App\Insights\Sources;

use App\Insights\Contracts\MetricSource;
use App\Insights\MetricDrill;
use App\Insights\MetricFilter;
use App\Insights\MetricMeta;
use App\Insights\MetricValue;
use App\Services\PartnerSuccess\MetricService;

/**
 * First adapter of the MetricSource contract: one audited warehouse metric,
 * delegating to the existing MetricService. Row-level visibility is applied by
 * threading the filter's allowedPartners into MetricService::restrictTo().
 */
class WarehouseMetricSource implements MetricSource
{
    public function __construct(
        private readonly MetricService $metrics,
        private readonly string $name,
        private readonly string $title,
        private readonly string $unit = 'count',
        private readonly ?string $section = null,
    ) {}

    public function key(): string
    {
        return $this->name;
    }

    public function meta(): MetricMeta
    {
        return new MetricMeta($this->name, $this->title, $this->unit, null, $this->section, 'warehouse');
    }

    public function value(MetricFilter $filter): MetricValue
    {
        $this->metrics->restrictTo($filter->allowedPartners)->withScope($filter->scope);

        foreach ($this->metrics->metrics($filter->geo, $filter->partner) as $m) {
            if ($m['name'] === $this->name) {
                return new MetricValue(
                    (int) $m['count'],
                    $m['acv'] !== null ? (int) $m['acv'] : null,
                    $m['unit'] ?? $this->unit,
                    $m['snapshot_date'] ?? null,
                );
            }
        }

        return new MetricValue(0, null, $this->unit);
    }

    public function drill(MetricFilter $filter): MetricDrill
    {
        $this->metrics->restrictTo($filter->allowedPartners)->withScope($filter->scope);
        $d = $this->metrics->drill($this->name, $filter->geo, $filter->partner);

        return new MetricDrill(
            $d['rows'] ?? [],
            $d['columns'] ?? [],
            $d['definition_sql'] ?? null,
            $d['provenance'] ?? [],
        );
    }

    public function trend(MetricFilter $filter): array
    {
        $this->metrics->restrictTo($filter->allowedPartners)->withScope($filter->scope);

        return $this->metrics->trend($this->name, $filter->geo, $filter->partner)['points'] ?? [];
    }

    public function breakdown(string $dimension, MetricFilter $filter): array
    {
        $this->metrics->restrictTo($filter->allowedPartners)->withScope($filter->scope);

        return $this->metrics->breakdown($dimension, $filter->geo, $filter->partner);
    }
}
