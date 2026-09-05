<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('workshops', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        Schema::table('workshop_sessions', function (Blueprint $table) {
            $table->timestamp('archived_at')->nullable()->after('status');
        });

        \App\Support\DatabasePortability::modifyEnum('workshop_sessions', 'status', ['upcoming', 'in_progress', 'completed', 'cancelled', 'archived'], true, 'upcoming');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Support\DatabasePortability::modifyEnum('workshop_sessions', 'status', ['upcoming', 'in_progress', 'completed', 'cancelled'], true, 'upcoming');

        Schema::table('workshops', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });

        Schema::table('workshop_sessions', function (Blueprint $table) {
            $table->dropColumn('archived_at');
        });
    }
};
