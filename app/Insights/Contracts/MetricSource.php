<?php

namespace App\Insights\Contracts;

use App\Insights\MetricDrill;
use App\Insights\MetricFilter;
use App\Insights\MetricMeta;
use App\Insights\MetricValue;

/**
 * A metric that knows how to compute itself AND how to explain itself. Any domain
 * (warehouse, app DB, …) implements this so the Insight Kit can render a value,
 * a drill (audit trail), a trend, and breakdowns for it — uniformly, and with
 * row-level visibility applied by the caller via MetricFilter::$allowedPartners.
 */
interface MetricSource
{
    public function key(): string;

    public function meta(): MetricMeta;

    public function value(MetricFilter $filter): MetricValue;

    public function drill(MetricFilter $filter): MetricDrill;

    /** @return array<int, array<string, mixed>> */
    public function trend(MetricFilter $filter): array;

    /** @return array<int, array<string, mixed>> */
    public function breakdown(string $dimension, MetricFilter $filter): array;
}
