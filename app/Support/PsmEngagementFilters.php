<?php

namespace App\Support;

use Illuminate\Support\Facades\Session;

/**
 * Session-backed scope for the PSM Engagement screens. Selected once in the
 * pinned filter panel and applied to every metric / chart / drill for the
 * current login session (per user). One source of truth read by both
 * PartnerSuccessController and the /insights endpoint.
 */
class PsmEngagementFilters
{
    private const KEY = 'psm_engagement.filters';

    /** @var array<int, string> */
    public const DIMENSIONS = ['geos', 'partners', 'lobs', 'stages', 'quarters', 'capGroups'];

    /**
     * @return array<string, array<string>>
     */
    public static function get(): array
    {
        $stored = Session::get(self::KEY, []);
        $out = [];
        foreach (self::DIMENSIONS as $d) {
            $out[$d] = array_values(array_filter((array) ($stored[$d] ?? []), fn ($v) => $v !== null && $v !== ''));
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public static function put(array $filters): void
    {
        $clean = [];
        foreach (self::DIMENSIONS as $d) {
            $vals = array_values(array_filter((array) ($filters[$d] ?? []), fn ($v) => $v !== null && $v !== ''));
            if ($vals) {
                $clean[$d] = $vals;
            }
        }
        Session::put(self::KEY, $clean);
    }

    public static function clear(): void
    {
        Session::forget(self::KEY);
    }

    public static function isActive(): bool
    {
        return (bool) array_filter(self::get());
    }
}
