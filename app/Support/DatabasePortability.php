<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DatabasePortability
{
    /** @var string[] */
    public const SEVERITY_ORDER = ['critical', 'warning', 'info', 'positive'];

    /** @var string[] */
    public const PRIORITY_ORDER = ['critical', 'high', 'medium', 'low'];

    /** @var string[] */
    public const ACTION_STATUS_ORDER = ['overdue', 'in_progress', 'open', 'completed', 'cancelled'];

    /**
     * Generate a cross-database CASE WHEN expression for custom ordering.
     * Replaces MySQL's FIELD() function.
     *
     * @param  string[]  $values  Ordered list of values (first = position 0)
     */
    public static function orderByValues(string $column, array $values): string
    {
        $cases = [];
        foreach ($values as $index => $value) {
            $escaped = str_replace("'", "''", $value);
            $cases[] = "WHEN '{$escaped}' THEN {$index}";
        }

        return 'CASE '.$column.' '.implode(' ', $cases).' ELSE 999 END';
    }

    /**
     * Generate a cross-database date formatting expression.
     * Replaces MySQL's DATE_FORMAT().
     *
     * Supported format tokens: %Y (4-digit year), %m (2-digit month), %d (2-digit day).
     */
    public static function dateFormat(string $column, string $mysqlFormat, ?string $connection = null): string
    {
        $driver = self::getDriver($connection);

        return match ($driver) {
            'pgsql', 'oracle' => 'TO_CHAR('.$column.", '".self::mysqlFormatToPostgres($mysqlFormat)."')",
            'sqlite' => "strftime('".self::mysqlFormatToSqlite($mysqlFormat)."', ".$column.')',
            default => "DATE_FORMAT({$column}, '{$mysqlFormat}')",
        };
    }

    /**
     * Generate a cross-database expression for extracting the date portion.
     * Replaces MySQL's DATE() function for PostgreSQL/Oracle compatibility.
     */
    public static function extractDate(string $column, ?string $connection = null): string
    {
        $driver = self::getDriver($connection);

        return match ($driver) {
            'pgsql' => "CAST({$column} AS DATE)",
            'oracle' => "TRUNC({$column})",
            default => "DATE({$column})",
        };
    }

    /**
     * Generate a cross-database ISO year-week expression.
     * Replaces MySQL's YEARWEEK().
     */
    public static function yearWeek(string $column, ?string $connection = null): string
    {
        $driver = self::getDriver($connection);

        return match ($driver) {
            'pgsql', 'oracle' => "TO_CHAR({$column}, 'IYYY-IW')",
            'sqlite' => "strftime('%Y-%W', {$column})",
            default => "YEARWEEK({$column}, 1)",
        };
    }

    /**
     * Generate a cross-database LEAST() expression.
     * SQLite uses MIN() with multiple arguments as equivalent.
     */
    public static function least(string $expr1, string $expr2, ?string $connection = null): string
    {
        $driver = self::getDriver($connection);

        return match ($driver) {
            'sqlite' => "MIN({$expr1}, {$expr2})",
            default => "LEAST({$expr1}, {$expr2})",
        };
    }

    private static function getDriver(?string $connection = null): string
    {
        return DB::connection($connection)->getDriverName();
    }

    private static function mysqlFormatToPostgres(string $format): string
    {
        return strtr($format, [
            '%Y' => 'YYYY',
            '%m' => 'MM',
            '%d' => 'DD',
            '%H' => 'HH24',
            '%i' => 'MI',
            '%s' => 'SS',
        ]);
    }

    private static function mysqlFormatToSqlite(string $format): string
    {
        // SQLite strftime uses the same tokens as MySQL for these common cases
        return $format;
    }
}
