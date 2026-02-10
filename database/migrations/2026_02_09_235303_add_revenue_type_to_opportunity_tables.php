<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('revenue_type', 20)->default('influenced')->after('lead_source')
                ->comment('sourced = partner originated, influenced = partner involved');
        });

        Schema::table('opportunity_snapshots', function (Blueprint $table) {
            $table->string('revenue_type', 20)->default('influenced')->after('lead_source');
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('revenue_type');
        });

        Schema::table('opportunity_snapshots', function (Blueprint $table) {
            $table->dropColumn('revenue_type');
        });
    }
};
