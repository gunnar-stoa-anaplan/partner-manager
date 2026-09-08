<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extend the Business Plan headcount commit with a line-of-business dimension.
 * An empty lob ('') is the practice-wide commit shown in the role tiles; a
 * populated lob (a capability group: FIN / SCM / RPM / WFP / AI / Platform) is
 * a per-LOB commit shown in the "People committed by line of business" matrix.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('partner_plan_commits', function (Blueprint $table) {
            $table->string('lob')->default('')->after('partner_id');
            $table->dropUnique(['partner_id', 'role']);
            $table->unique(['partner_id', 'lob', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('partner_plan_commits', function (Blueprint $table) {
            $table->dropUnique(['partner_id', 'lob', 'role']);
            $table->unique(['partner_id', 'role']);
            $table->dropColumn('lob');
        });
    }
};
