<?php

namespace App\Contracts;

use App\Models\Partner;
use Illuminate\Support\Collection;

interface SignalProcessor
{
    public function name(): string;

    public function channel(): string;

    public function cadence(): string;

    public function process(Partner $partner): Collection;

    /**
     * Return processor metadata for the signal catalog.
     *
     * @return array{description: string, data_sources: string[], signal_types: array<string, array{severity: string, description: string}>, thresholds: array<string, mixed>}
     */
    public function describe(): array;
}
