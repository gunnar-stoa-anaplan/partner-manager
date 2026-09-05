<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Make "Partner Success" (Anaplan navy + orange) the default app-wide brand
 * theme. Idempotent and cross-database (plain query builder). Individual users
 * can still override via their own branding preference.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('app_settings')->updateOrInsert(
            ['key' => 'branding_theme'],
            ['value' => 'partner-success']
        );
    }

    public function down(): void
    {
        DB::table('app_settings')->where('key', 'branding_theme')->delete();
    }
};
