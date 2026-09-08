<?php

namespace App\Insights;

/**
 * The filter/scope applied to a metric computation. `allowedPartners` carries
 * row-level visibility (null = unrestricted); geo/partner are the UI filters.
 */
class MetricFilter
{
    /**
     * @param  array<string>|null  $allowedPartners
     * @param  array<string, array<string>>  $scope  multi-dimension panel scope
     */
    public function __construct(
        public readonly ?string $geo = null,
        public readonly ?string $partner = null,
        public readonly ?array $allowedPartners = null,
        public readonly array $scope = [],
    ) {}
}
