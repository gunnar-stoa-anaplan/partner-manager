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

    /**
     * Set the allowed values of an "enum" column, cross-database.
     * MySQL uses a native ENUM; PostgreSQL uses a CHECK constraint on a text
     * column (which is how Laravel's ->enum() already stores it on pgsql).
     *
     * @param  string[]  $values
     */
    public static function modifyEnum(
        string $table,
        string $column,
        array $values,
        bool $notNull = true,
        ?string $default = null,
        ?string $connection = null
    ): void {
        $driver = self::getDriver($connection);
        $db = DB::connection($connection);
        $quote = static fn (string $v): string => "'".str_replace("'", "''", $v)."'";
        $list = implode(', ', array_map($quote, $values));

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $sql = "ALTER TABLE {$table} MODIFY COLUMN {$column} ENUM({$list})";
            $sql .= $notNull ? ' NOT NULL' : ' NULL';
            if ($default !== null) {
                $sql .= ' DEFAULT '.$quote($default);
            }
            $db->statement($sql);

            return;
        }

        if ($driver === 'pgsql') {
            $check = "{$table}_{$column}_check";
            $db->statement("ALTER TABLE {$table} DROP CONSTRAINT IF EXISTS {$check}");
            $db->statement("ALTER TABLE {$table} ADD CONSTRAINT {$check} CHECK ({$column} IN ({$list}))");
            if ($default !== null) {
                $db->statement("ALTER TABLE {$table} ALTER COLUMN {$column} SET DEFAULT ".$quote($default));
            }
            $db->statement("ALTER TABLE {$table} ALTER COLUMN {$column} ".($notNull ? 'SET' : 'DROP').' NOT NULL');

            return;
        }
        // sqlite: enum is plain text with no constraint — nothing to enforce.
    }

    /**
     * Make an existing column nullable, cross-database.
     * `$mysqlType` is the full MySQL column type (e.g. 'BIGINT UNSIGNED').
     */
    public static function makeColumnNullable(
        string $table,
        string $column,
        string $mysqlType,
        ?string $connection = null
    ): void {
        $driver = self::getDriver($connection);
        $db = DB::connection($connection);

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $db->statement("ALTER TABLE {$table} MODIFY {$column} {$mysqlType} NULL");

            return;
        }

        if ($driver === 'pgsql') {
            $db->statement("ALTER TABLE {$table} ALTER COLUMN {$column} DROP NOT NULL");
        }
        // sqlite: column nullability changes require a table rebuild — skipped.
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
