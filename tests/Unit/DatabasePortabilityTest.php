<?php

namespace Tests\Unit;

use App\Support\DatabasePortability;
use PHPUnit\Framework\TestCase;

class DatabasePortabilityTest extends TestCase
{
    public function test_order_by_values_generates_case_expression(): void
    {
        $result = DatabasePortability::orderByValues('status', ['open', 'closed']);

        $this->assertStringContainsString('CASE status', $result);
        $this->assertStringContainsString("WHEN 'open' THEN 0", $result);
        $this->assertStringContainsString("WHEN 'closed' THEN 1", $result);
        $this->assertStringContainsString('ELSE 999 END', $result);
    }

    public function test_order_by_values_escapes_single_quotes(): void
    {
        $result = DatabasePortability::orderByValues('name', ["it's"]);

        $this->assertStringContainsString("WHEN 'it''s' THEN 0", $result);
    }

    public function test_order_by_values_with_severity_constant(): void
    {
        $result = DatabasePortability::orderByValues('severity', DatabasePortability::SEVERITY_ORDER);

        $this->assertStringContainsString("WHEN 'critical' THEN 0", $result);
        $this->assertStringContainsString("WHEN 'warning' THEN 1", $result);
        $this->assertStringContainsString("WHEN 'info' THEN 2", $result);
        $this->assertStringContainsString("WHEN 'positive' THEN 3", $result);
    }

    public function test_order_by_values_with_priority_constant(): void
    {
        $result = DatabasePortability::orderByValues('priority', DatabasePortability::PRIORITY_ORDER);

        $this->assertStringContainsString("WHEN 'critical' THEN 0", $result);
        $this->assertStringContainsString("WHEN 'low' THEN 3", $result);
    }

    public function test_order_by_values_with_action_status_constant(): void
    {
        $result = DatabasePortability::orderByValues('status', DatabasePortability::ACTION_STATUS_ORDER);

        $this->assertStringContainsString("WHEN 'overdue' THEN 0", $result);
        $this->assertStringContainsString("WHEN 'cancelled' THEN 4", $result);
    }

    public function test_severity_order_constant(): void
    {
        $this->assertEquals(['critical', 'warning', 'info', 'positive'], DatabasePortability::SEVERITY_ORDER);
    }

    public function test_priority_order_constant(): void
    {
        $this->assertEquals(['critical', 'high', 'medium', 'low'], DatabasePortability::PRIORITY_ORDER);
    }

    public function test_action_status_order_constant(): void
    {
        $this->assertEquals(
            ['overdue', 'in_progress', 'open', 'completed', 'cancelled'],
            DatabasePortability::ACTION_STATUS_ORDER
        );
    }

    public function test_mysql_format_to_postgres_converts_tokens(): void
    {
        $method = new \ReflectionMethod(DatabasePortability::class, 'mysqlFormatToPostgres');

        $this->assertEquals('YYYY-MM', $method->invoke(null, '%Y-%m'));
        $this->assertEquals('YYYY-MM-DD', $method->invoke(null, '%Y-%m-%d'));
        $this->assertEquals('HH24:MI:SS', $method->invoke(null, '%H:%i:%s'));
    }

    public function test_mysql_format_to_sqlite_passes_through(): void
    {
        $method = new \ReflectionMethod(DatabasePortability::class, 'mysqlFormatToSqlite');

        $this->assertEquals('%Y-%m', $method->invoke(null, '%Y-%m'));
    }
}
