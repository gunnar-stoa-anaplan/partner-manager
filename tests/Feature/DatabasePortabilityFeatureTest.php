<?php

namespace Tests\Feature;

use App\Support\DatabasePortability;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabasePortabilityFeatureTest extends TestCase
{
    public function test_date_format_returns_valid_sql_for_current_driver(): void
    {
        $sql = DatabasePortability::dateFormat('created_at', '%Y-%m');

        $driver = DB::connection()->getDriverName();
        match ($driver) {
            'mysql' => $this->assertStringContainsString('DATE_FORMAT', $sql),
            'pgsql', 'oracle' => $this->assertStringContainsString('TO_CHAR', $sql),
            'sqlite' => $this->assertStringContainsString('strftime', $sql),
            default => $this->assertTrue(true),
        };
    }

    public function test_extract_date_returns_valid_sql_for_current_driver(): void
    {
        $sql = DatabasePortability::extractDate('created_at');

        $driver = DB::connection()->getDriverName();
        match ($driver) {
            'mysql', 'sqlite' => $this->assertStringContainsString('DATE(', $sql),
            'pgsql' => $this->assertStringContainsString('CAST(', $sql),
            'oracle' => $this->assertStringContainsString('TRUNC(', $sql),
            default => $this->assertTrue(true),
        };
    }

    public function test_year_week_returns_valid_sql_for_current_driver(): void
    {
        $sql = DatabasePortability::yearWeek('created_at');

        $driver = DB::connection()->getDriverName();
        match ($driver) {
            'mysql' => $this->assertStringContainsString('YEARWEEK', $sql),
            'pgsql', 'oracle' => $this->assertStringContainsString('TO_CHAR', $sql),
            'sqlite' => $this->assertStringContainsString('strftime', $sql),
            default => $this->assertTrue(true),
        };
    }

    public function test_least_returns_valid_sql_for_current_driver(): void
    {
        $sql = DatabasePortability::least('a', 'b');

        $driver = DB::connection()->getDriverName();
        match ($driver) {
            'sqlite' => $this->assertStringContainsString('MIN(', $sql),
            default => $this->assertStringContainsString('LEAST(', $sql),
        };
    }

    public function test_date_format_produces_executable_sql(): void
    {
        $dateExpr = DatabasePortability::dateFormat('NOW()', '%Y-%m');

        $result = DB::selectOne("SELECT {$dateExpr} as formatted");

        $this->assertNotNull($result->formatted);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}$/', $result->formatted);
    }

    public function test_extract_date_produces_executable_sql(): void
    {
        $dateExpr = DatabasePortability::extractDate('NOW()');

        $result = DB::selectOne("SELECT {$dateExpr} as date_val");

        $this->assertNotNull($result->date_val);
    }
}
